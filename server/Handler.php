<?php

declare(strict_types=1);

namespace site;

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
use site\File;
use site\dbobject\SessionEvent;
use site\dbobject\User;

abstract class Handler extends CoreHandler
{
    const UID_GUEST = 0;
    const UID_ADMIN = 1;

    const LIMIT_ROBOT = 20;
    const LIMIT_GUEST = 50;
    const LIMIT_USER = 100;
    const LIMIT_WINDOW = 86400;

    public $args;
    public $session;
    public $config;

    protected static City $city;
    private static CacheHandler $cacheHandler;
    protected ?PageCache $cache = null;
    protected array $independentCacheList = [];
    protected array $cacheEvents = [];

    public function __construct(Request $req, Response $response, Config $config, Logger $logger, Session $session, array $args)
    {
        parent::__construct($req, $response, $logger);
        $this->session = $session;
        $this->config = $config;
        $this->args = $args;
    }

    public function beforeRun(): void
    {
        $this->rateLimit();
        $this->dedup();
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
            if (self::$city->id != $this->session->get('cid')) {
                $this->session->set('cid', self::$city->id);
            }
        } else {
            $this->logger->error('unsupported website: ' . $this->request->domain);
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
                throw new ErrorMessage("此信息已经提交，不能重复提交。");
            }
        }
    }

    public function rateLimit(): void
    {
        $rateLimiter = MemStore::getRedis(MemStore::RATE);
        $handler = str_replace(['site\\handler\\', '\\Handler', '\\'], ':', static::class);
        if ($this->session->get('uid')) {
            $key = date("d") . $handler . $this->session->get('uid');
            $limit = static::LIMIT_USER * 2;
            $window = static::LIMIT_WINDOW / 2;
        } else {
            $key = date("d") . $handler . $this->request->ip;
            if ($this->request->isRobot()) {
                $limit = static::LIMIT_ROBOT;
                $window = static::LIMIT_WINDOW;
            } else {
                $limit = static::LIMIT_GUEST;
                $window = static::LIMIT_WINDOW;
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
                $limit *= 100;
            }

            if ($current > $limit) {
                // mail error log
                if (!$rateLimiter->exists($key . ':log')) {
                    if ($this->request->isRobot()) {
                        $this->logger->warning('rate limit ' . $this->request->ip);
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
        if ($this->request->uri !== '/api/stat' || $this->request->uid === self::UID_GUEST) {
            return;
        }

        $user = new User($this->request->uid, 'lastAccessTime');
        if ($this->request->timestamp - $user->lastAccessTime > 259200) { // 3 days
            $user->lastAccessTime = $this->request->timestamp;
            $user->update();

            $this->updateSessionEvent(SessionEvent::EVENT_UPDATE);
        }
    }

    public function updateSessionEvent(string $event)
    {
        $sessionEvent = new SessionEvent();
        $sessionEvent->sessionId = $this->session->id();
        $sessionEvent->userId = $this->session->get('uid');
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

    protected function validateUserExists(int $uid): void
    {
        if ($uid > 0) {
            $user = new User($uid, 'status');
            if ($user->exists() && $user->status > 0) {
                return;
            }
        }

        throw new ErrorMessage('用户不存在');
    }

    protected function validateUser(): void
    {
        if ($this->request->uid === self::UID_GUEST) {
            throw new ErrorMessage('请先登陆');
        }

        $this->validateUserExists($this->request->uid);
    }

    protected function deleteUser(int $uid): void
    {
        if ($uid < 2) {
            throw new InvalidArgumentException((string) $uid);
        }

        $user = new User();
        $user->id = $uid;
        $user->delete();

        $sessionEvent = new SessionEvent();
        $sessionId = $sessionEvent->getSessionId($uid);
        if ($sessionId) {
            $this->session->deleteSession($sessionId);
        }

        foreach ($user->getAllNodeIDs() as $nid) {
            $this->getIndependentCache('/node/' . $nid)->delete();
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
                $saveName = $this->request->timestamp . $this->request->uid;
                $upload = File::saveFiles($_FILES, $saveDir, $saveName, $this->config->image);
                foreach ($upload['saved'] as $f) {
                    $f['name'] = $new[$f['name']]['name'];
                    $files[$f['path']] = $f;
                }
            }
        }
        return $files;
    }
}
