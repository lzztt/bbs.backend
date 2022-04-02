<?php

declare(strict_types=1);

namespace site;

use Exception;
use InvalidArgumentException;
use lzx\cache\Cache;
use lzx\cache\CacheEvent;
use lzx\cache\CacheHandler;
use lzx\cache\PageCache;
use lzx\core\Handler as CoreHandler;
use lzx\core\Logger;
use lzx\core\Request;
use lzx\core\Response;
use lzx\db\MemStore;
use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use lzx\exception\Redirect;
use lzx\geo\Reader;
use site\File;
use site\dbobject\SessionEvent;
use site\dbobject\SpamWord;
use site\dbobject\User;
use site\handler\api\message\Handler as MessageHandler;
use site\handler\forum\node\Handler as NodeHandler;

abstract class Handler extends CoreHandler
{
    const UID_GUEST = 0;
    const UID_ADMIN = 1;

    const LIMIT_ROBOT = 10;
    const LIMIT_GUEST = 50;
    const LIMIT_USER = 100;

    const ONE_DAY = 86400;

    public array $args;
    public Session $session;
    public Config $config;

    protected static City $city;
    private static CacheHandler $cacheHandler;

    protected User $user;
    protected ?PageCache $cache = null;
    protected array $independentCacheList = [];
    protected array $cacheEvents = [];

    public function __construct(Request $req, Response $response, Config $config, Logger $logger, Session $session, array $args)
    {
        parent::__construct($req, $response, $logger);
        $this->session = $session;
        $this->config = $config;
        $this->args = $args;

        $this->user = new User();
        $this->user->id = $session->get('uid');
    }

    public function beforeRun(): void
    {
        $this->rateLimit();
        $this->staticInit();
    }

    public function afterRun(): void
    {
        $this->updateAccessInfo();
    }

    protected function staticInit(): void
    {
        static $initialized = false;

        if ($initialized) {
            return;
        }

        $initialized = true;

        self::$city = $this->config->city;
        // set site info
        $site = str_replace(['bbs.com', '.com'], '', self::$city->domain);

        self::$cacheHandler = CacheHandler::getInstance();
        self::$cacheHandler->setDomain($site);

        // validate site for session
        if (self::$city->id) {
            $this->session->setCityId(self::$city->id);
        } else {
            $this->logger->error('unsupported website: ' . $this->request->domain);
        }
    }

    public function postTopic(string $title, string $body): void
    {
        $handler = new NodeHandler(clone $this->request, clone $this->response, $this->config, $this->logger, $this->session, $this->args);
        $handler->user->id = self::UID_ADMIN;
        $handler->request->data = [
            'title' => $title,
            'body' => $body,
        ];

        try {
            $handler->createTopic(self::$city->tidSystem);
        } catch (Redirect $e) {
            $this->logger->info('system topic:' . $e->getMessage());
        } catch (Exception $e) {
            $this->logger->error('system topic error:' . $e->getMessage());
        }

        $handler->flushCache();
    }

    public function sendMessage(int $toUid, string $body): void
    {
        if ($toUid === self::UID_ADMIN) {
            return;
        }

        $handler = new MessageHandler(clone $this->request, clone $this->response, $this->config, $this->logger, $this->session, $this->args);
        $handler->user->id = self::UID_ADMIN;
        $handler->request->data = [
            'toUid' => $toUid,
            'body' => $body,
        ];

        try {
            $handler->post();
        } catch (Exception $e) {
            $this->logger->error('system message error:' . $e->getMessage());
        }
    }

    public function dedup(): void
    {
        if (array_key_exists('formId', $this->request->data)) {
            $deduper = MemStore::getRedis(MemStore::DEDUP);
            $key = 'f:' . $this->request->data['formId'];
            $count = $deduper->incr($key);
            $deduper->expire($key, 3600);

            if ($count > 1) {
                throw new Exception("此信息已经提交，不能重复提交。");
            }
        }
    }

    public function dedupContent(string $text): void
    {
        if ($text) {
            $clean = $this->cleanText($text, []);
            if (strlen($clean) < 2) {
                throw new Exception("内容太短了。");
            }

            $deduper = MemStore::getRedis(MemStore::DEDUP);
            $key = 'c:' . $this->user->id . ':' . md5($clean);
            $count = $deduper->incr($key);
            $deduper->expire($key, self::ONE_DAY);

            if ($count > 1) {
                throw new Exception("请维护好论坛的交流环境，不要一帖多发。");
            }
        }
    }

    public function rateLimit(): void
    {
        if ($this->user->id) {
            $limit = static::LIMIT_USER * 2;
            $window = static::ONE_DAY / 2;
        } else {
            if ($this->request->isRobot()) {
                $limit = static::LIMIT_ROBOT;
                $window = static::ONE_DAY;
            } else {
                $limit = static::LIMIT_GUEST;
                $window = static::ONE_DAY;
            }
        }

        if ($limit < 0) {
            return;
        }

        $rateLimiter = MemStore::getRedis(MemStore::RATE);
        $handler = str_replace(['site\\handler\\', '\\Handler', '\\'], ':', static::class);
        $key = date("d") . $handler . ($this->user->id ? $this->user->id : $this->request->ip);

        $current = $rateLimiter->incr($key);
        $rateLimiter->expire($key, $window);

        if ($current > $limit) {
            // check if allowed bots
            $isAllowedBot = false;
            if ($this->request->isRobot()) {
                $botKey = 'b:' . $this->request->ip;
                if ($rateLimiter->exists($botKey)) {
                    $isAllowedBot = boolval($rateLimiter->get($botKey));
                } else {
                    $isAllowedBot = $this->request->isGoogleBot();
                    if (!$isAllowedBot) {
                        $isAllowedBot = $this->request->isBingBot();
                    }
                    $rateLimiter->set($botKey, $isAllowedBot ? '1' : '0', $window);
                }
            }

            if ($isAllowedBot) {
                $limit *= 200;
            }

            if ($current > $limit) {
                if ($limit === 0 && $this->user->id === self::UID_GUEST && !$this->request->isRobot()) {
                    $this->session->regenerateId();
                }
                // log error
                if (!$rateLimiter->exists($key . ':log')) {
                    if ($this->request->isRobot() || ($limit === 0 && $this->user->id === self::UID_GUEST)) {
                        $this->logger->warning('rate limit ' . $this->request->ip . ' : ' . $this->request->agent);
                    } else {
                        $this->logger->error('rate limit ' . $this->request->ip);
                    }

                    $rateLimiter->set($key . ':log', '', $window);
                }
                throw new Forbidden();
            }
        }
    }

    private function updateAccessInfo(): void
    {
        if ($this->request->uri !== '/api/stat' || $this->user->id === self::UID_GUEST) {
            return;
        }

        $sessionEvent = new SessionEvent();
        $sessionEvent->userId = $this->user->id;
        $sessionEvent->ip = inet_pton($this->request->ip);
        $sessionEvent->hash = crc32($this->request->agent);
        $sessionEvent->load('time,count');

        if ($sessionEvent->exists()) {
            if ($this->request->timestamp - $sessionEvent->time > self::ONE_DAY * 3) {
                $sessionEvent->time = $this->request->timestamp;
                $sessionEvent->count += 1;
                $sessionEvent->update('time,count');
            }
        } else {
            if (strlen($this->request->agent) < 256) {
                $agent = $this->request->agent;
            } else {
                $agent = substr($this->request->agent, 0, 255);
                $this->logger->warning("Long user agent: " . $this->request->agent);
            }
            $sessionEvent->agent = $agent;
            $sessionEvent->time = $this->request->timestamp;
            $sessionEvent->count = 1;
            $sessionEvent->add();
        }
    }

    public function flushCache(): void
    {
        if ($this->config->cache) {
            if ($this->cache && $this->response->getStatus() < 300) {
                $this->response->cacheContent($this->cache);
                $this->cache->flush();
                $this->cache = null;
            }

            foreach ($this->independentCacheList as $c) {
                $c->flush();
            }

            foreach ($this->cacheEvents as $e) {
                $e->flush();
            }
        }
    }

    protected function getSiteName(): string
    {
        $siteName = str_replace('.com', '', self::$city->domain);
        if (substr($siteName, -3) === 'bbs') {
            $siteName = ucfirst(substr($siteName, 0, -3)) . 'BBS';
        }
        return $siteName;
    }

    protected function getPageCache(): PageCache
    {
        return new PageCache($this->request->uri, self::$cacheHandler);
    }

    protected function getIndependentCache(string $key): Cache
    {
        $key = self::$cacheHandler->cleanKey($key);
        if (array_key_exists($key, $this->independentCacheList)) {
            return $this->independentCacheList[$key];
        } else {
            $cache = self::$cacheHandler->createCache($key);
            $this->independentCacheList[$key] = $cache;
            return $cache;
        }
    }

    protected function getCacheEvent(string $name, int $objectID = 0): CacheEvent
    {
        $name = self::$cacheHandler->cleanKey($name);
        $objID = (int) $objectID;
        if ($objID < 0) {
            $objID = 0;
        }

        $key = $name . $objID;
        if (array_key_exists($key, $this->cacheEvents)) {
            return $this->cacheEvents[$key];
        } else {
            $event = new CacheEvent($name, $objID);
            $this->cacheEvents[$key] = $event;
            return $event;
        }
    }

    protected function validateUser(bool $checkUsername = true): void
    {
        if ($this->user->id === self::UID_GUEST) {
            throw new ErrorMessage('请先登陆');
        }

        if (is_null($this->user->status)) {
            $this->user->load();
        }

        if (!$this->user->exists() || $this->user->status < 1) {
            throw new ErrorMessage('用户不存在');
        }

        if ($checkUsername && empty($this->user->username)) {
            throw new ErrorMessage('您尚未设置用户名，请重新登陆。');
        }

        if ($this->user->reputation + $this->user->contribution < -2) {
            throw new ErrorMessage('用户的社区声望和贡献不足。');
        }
    }

    protected function deleteUser(int $uid): void
    {
        if ($uid < 2) {
            throw new InvalidArgumentException((string) $uid);
        }

        $user = new User();
        $user->id = $uid;
        $user->delete();

        $this->logoutUser($uid);

        foreach ($user->getAllNodeIDs() as $nid) {
            $this->getIndependentCache('/node/' . $nid)->delete();
        }
    }

    protected function logoutUser(int $uid): void
    {
        $this->session->deleteSessions($uid);
    }

    protected function getFormFiles(): array
    {
        $files = [];
        if (!empty($this->request->data['file_id']) && is_array($this->request->data['file_id'])) {
            $ids = $this->request->data['file_id'];
            $names = $this->request->data['file_name'];
            $new = [];
            for ($i = 0; $i < count($ids); $i++) {
                if (strlen($ids[$i]) === 3) {
                    $new[$ids[$i]]['name'] = $names[$i];
                } else {
                    $files[$ids[$i]]['name'] = $names[$i];
                }
            }

            if (count($new) && count($_FILES)) {
                $saveDir = $this->config->path['file'];
                $saveName = $this->request->timestamp . $this->user->id;
                $upload = File::saveFiles($_FILES, $saveDir, $saveName, $this->config->image);
                foreach ($upload['saved'] as $f) {
                    $f['name'] = $new[$f['name']]['name'];
                    $files[$f['path']] = $f;
                }
            }
        }
        return $files;
    }

    protected function validatePost(): void
    {
        $this->validateUser();

        $creationDays = (int) (($this->request->timestamp - $this->user->createTime) / self::ONE_DAY);
        if ($creationDays < 30) {
            $spamwords = (new SpamWord())->getList();

            if (array_key_exists('title', $this->request->data)) {
                $this->checkTitle($this->request->data['title'], $spamwords);
            }

            $this->checkBody($this->request->data['body'], $spamwords);

            if ($creationDays < 10 && self::$city->id === City::HOUSTON) {
                $this->checkPostCounts($this->user, $creationDays);
            }
        }
    }

    protected function checkTitle(string $title, array $spamwords): void
    {
        $cleanTitle = $this->cleanText($title, array_column(
            array_filter($spamwords, function (array $record): bool {
                return (bool) $record['title'];
            }),
            'word'
        ));

        if ($title && mb_strlen($title) - mb_strlen($cleanTitle) > 4) {
            throw new Exception('标题太短了。');
        }
    }

    protected function checkBody(string $body, array $spamwords): void
    {
        $cleanBody = $this->cleanText($body, array_column($spamwords, 'word'));

        $bodyLen = mb_strlen($body);
        if ($bodyLen > 35 && ($bodyLen - mb_strlen($cleanBody)) / $bodyLen > 0.4) {
            throw new Exception('正文太短了。');
        }
    }

    private function cleanText(string $text, array $spamwords): string
    {
        $cleanText = preg_replace('#[^\p{Nd}\p{Han}\p{Latin}\s$/]+#u', '', $text);

        foreach ($spamwords as $w) {
            if (mb_strpos($cleanText, $w) !== false) {
                $this->deleteSpammer();
                throw new Exception('用户被封禁，不能发布信息！');
            }
        }

        return $cleanText;
    }

    protected function checkPostCounts(User $user, int $creationDays): void
    {
        $geo = Reader::getInstance()->get($this->request->ip);
        if ($geo->city->en === 'Nanning') {
            $this->deleteSpammer();
            throw new Exception('用户被封禁，不能发布信息！');
        }

        if ($geo->region->en !== 'Texas') {
            $postCount = (int) array_pop(array_pop($user->call('get_user_post_count(' . $user->id . ')')));
            if ($postCount >= $creationDays) {
                throw new Exception('您的发帖数已达上限：' . $creationDays . '。请等待一天再发帖。');
            }
        }
    }

    protected function deleteSpammer(): void
    {
        $this->logger->info('SPAMMER DELETED: uid=' . $this->user->id);
        $this->deleteUser($this->user->id);

        $u = new User();
        $u->lastAccessIp = inet_pton($this->request->ip);
        $u->status = 1;
        $users = $u->getList('createTime');

        $deleteAll = true;
        foreach ($users as $u) {
            if ($this->request->timestamp - (int) $u['createTime'] > 2592000) {
                $deleteAll = false;
                break;
            }
        }

        if ($deleteAll) {
            $this->logger->info('SPAMMER DELETED (IP=' . $this->request->ip . '): uid=' . implode(',', array_column($users, 'id')));
            foreach ($users as $u) {
                $this->deleteUser((int) $u['id']);
            }
        }
    }
}
