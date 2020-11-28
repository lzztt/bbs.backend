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
            if (self::$city->id !== $this->session->get('cid')) {
                $this->session->set('cid', self::$city->id);
            }
        } else {
            $this->logger->error('unsupported website: ' . $this->request->domain);
        }
    }

    public function postTopic(string $title, string $body): void
    {
        $handler = new NodeHandler($this->request, $this->response, $this->config, $this->logger, $this->session, $this->args);
        $handler->user->id = self::UID_ADMIN;
        $handler->request->data = [
            'title' => $title,
            'body' => $body,
        ];

        try {
            $handler->createTopic(25);
        } catch (Redirect $e) {
            $this->logger->info('posting system topic:' . $e->getMessage());
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
        $rateLimiter = MemStore::getRedis(MemStore::RATE);
        $handler = str_replace(['site\\handler\\', '\\Handler', '\\'], ':', static::class);
        if ($this->user->id) {
            $key = date("d") . $handler . $this->user->id;
            $limit = static::LIMIT_USER * 2;
            $window = static::ONE_DAY / 2;
        } else {
            $key = date("d") . $handler . $this->request->ip;
            if ($this->request->isRobot()) {
                $limit = static::LIMIT_ROBOT;
                $window = static::ONE_DAY;
            } else {
                $limit = static::LIMIT_GUEST;
                $window = static::ONE_DAY;
            }
        }

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

        if ($this->request->timestamp - $this->user->lastAccessTime > 259200) { // 3 days
            $this->user->lastAccessTime = $this->request->timestamp;
            $this->user->update('lastAccessTime');

            $this->updateSessionEvent(SessionEvent::EVENT_UPDATE);
        }
    }

    public function updateSessionEvent(string $event)
    {
        $sessionEvent = new SessionEvent();
        $sessionEvent->sessionId = $this->session->id();
        $sessionEvent->userId = $this->user->id;
        $sessionEvent->event = $event;
        $sessionEvent->time = $this->request->timestamp;
        $sessionEvent->ip = inet_pton($this->request->ip);
        if (strlen($this->request->agent) < 256) {
            $sessionEvent->agent = $this->request->agent;
        } else {
            $sessionEvent->agent = substr($this->request->agent, 0, 255);
            $this->logger->warning("Long user agent: " . $this->request->agent);
        }
        $sessionEvent->add();
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

    protected function validateUser(): void
    {
        if ($this->user->id === self::UID_GUEST) {
            throw new ErrorMessage('请先登陆');
        }

        if (is_null($this->user->status)) {
            $this->user->load();
        }

        if ($this->user->exists() && $this->user->status > 0) {
            return;
        }

        throw new ErrorMessage('用户不存在');
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
        $sessionEvent = new SessionEvent();
        $sessionId = $sessionEvent->getSessionId($uid);
        if ($sessionId) {
            $this->session->deleteSession($sessionId);
        }
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

            if ($creationDays < 10 && self::$city->domain === 'houstonbbs.com') {
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
            throw new Exception('Title is not valid!');
        }
    }

    protected function checkBody(string $body, array $spamwords): void
    {
        $cleanBody = $this->cleanText($body, array_column($spamwords, 'word'));

        $bodyLen = mb_strlen($body);
        if ($bodyLen > 35 && ($bodyLen - mb_strlen($cleanBody)) / $bodyLen > 0.4) {
            throw new Exception('Body text is not valid!');
        }
    }

    private function cleanText(string $text, array $spamwords): string
    {
        $cleanText = preg_replace('#[^\p{Nd}\p{Han}\p{Latin}\s$/]+#u', '', $text);

        foreach ($spamwords as $w) {
            if (mb_strpos($cleanText, $w) !== false) {
                $this->deleteSpammer();
                throw new Exception('User is blocked! You cannot post any message!');
            }
        }

        return $cleanText;
    }

    protected function checkPostCounts(User $user, int $creationDays): void
    {
        $geo = Reader::getInstance()->get($this->request->ip);
        if ($geo->city->en === 'Nanning') {
            $this->deleteSpammer();
            throw new Exception('User is blocked! You cannot post any message!');
        }

        if ($geo->region->en !== 'Texas') {
            $postCount = (int) array_pop(array_pop($user->call('get_user_post_count(' . $user->id . ')')));
            if ($postCount >= $creationDays) {
                throw new Exception('Quota Limit Exceeded! You can only post no more than ' . $creationDays . ' messages up to now. Please wait one day to get more quota.');
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
