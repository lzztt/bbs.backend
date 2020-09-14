<?php declare(strict_types=1);

namespace site;

use InvalidArgumentException;
use lzx\cache\Cache;
use lzx\cache\CacheEvent;
use lzx\cache\CacheHandler;
use lzx\cache\PageCache;
use lzx\exception\ErrorMessage;
use lzx\html\Template;
use site\File;
use site\dbobject\User;
use stdClass;

trait HandlerTrait
{
    protected static stdClass $city;
    private static CacheHandler $cacheHandler;
    protected ?PageCache $cache = null;
    protected array $independentCacheList = [];
    protected array $cacheEvents = [];

    private function staticInit(): void
    {
        static $initialized = false;

        if ($initialized) {
            return;
        }

        $initialized = true;

        self::$city = $this->config->city;
        // set site info
        $site = str_replace(['bbs.com', '.com'], '', self::$city->domain);

        Template::setSite($site);

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

        $this->updateAccessInfo();
    }

    private function updateAccessInfo(): void
    {
        if ($this->request->uid === self::UID_GUEST) {
            return;
        }

        $user = new User($this->request->uid, 'lastAccessTime,lastAccessIp');
        if ($this->request->timestamp - $user->lastAccessTime > 900) {
            $user->lastAccessTime = $this->request->timestamp;
            $ip = inet_pton($this->request->ip);
            if ($user->lastAccessIp !== $ip) {
                $user->lastAccessIp = $ip;
            }
            $user->update();
        }
    }

    public function flushCache(): void
    {
        if ($this->config->cache) {
            if ($this->cache && $this->response->getStatus() < 300 && Template::hasError() === false) {
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
            $user = new User();
            $user->id = $uid;
            if ($user->exists()) {
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
