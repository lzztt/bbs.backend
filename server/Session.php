<?php

declare(strict_types=1);

namespace site;

use Redis;
use lzx\db\MemStore;

class Session
{
    private const FIVE_MINUTES = 300;
    private const ONE_DAY = 86400;
    private const ONE_MONTH = 2592000;
    private const SID_NAME = 'LZXSID';
    private const JSON_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    private int $cityId = 0;
    private string $id = '';
    private string $originalId = '';
    private int $originalTtl = 0;
    private ?Redis $redis = null;
    private ?Redis $redisOnline = null;
    private array $original = [];
    private array $current = [];


    public function __construct(bool $useDb)
    {
        if (!$useDb) {
            return;
        }

        $this->redis = MemStore::getRedis(MemStore::SESSION);
        $this->redisOnline = MemStore::getRedis(MemStore::ONLINE);

        if (!empty($_COOKIE[self::SID_NAME])) {
            $this->id = $_COOKIE[self::SID_NAME];
        }

        $this->loadDbSession();
    }

    private function loadDbSession(): void
    {
        if (!$this->id) {
            return;
        }

        $key = $this->getKey($this->id);
        $type = $this->redis->type($key);
        if ($type === Redis::REDIS_NOT_FOUND) {
            return;
        }
        $isOld = $type === Redis::REDIS_HASH;
        $data = $isOld ? $this->redis->hGetAll($key) : $this->redis->get($key);
        if (!$data) {
            return;
        }

        $this->originalId = $this->id;
        if ($isOld) {
            $this->original = self::decodeData($data['data']);
            $this->original['uid'] = (int) $data['uid'];
        } else {
            $this->original = self::decodeData($data);
        }

        $this->current = $this->original;

        if ($isOld) {
            $this->regenerateId();
            return;
        }

        $this->originalTtl = (int) $this->redis->ttl($key);
        if (
            $this->originalTtl < self::ONE_MONTH - self::ONE_DAY
            && $this->get('uid') > 0
        ) {
            $this->regenerateId();
        }
    }

    public function regenerateId(): void
    {
        if (!$this->redis) {
            return;
        }

        $this->id = bin2hex(random_bytes(8));

        setcookie(self::SID_NAME, $this->id, [
            'expires' => (int) $_SERVER['REQUEST_TIME'] + ($this->get('uid') > 0
                ? self::ONE_MONTH
                : self::ONE_DAY),
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => false,
            'samesite' => 'Strict'
        ]);
    }

    public function setCityId(int $cityId): void
    {
        $this->cityId = $cityId;
    }

    private static function encodeData(array $data): string
    {
        return $data ? json_encode($data, self::JSON_OPTIONS) : '';
    }

    private static function decodeData(string $data): array
    {
        if (!$data) {
            return [];
        }

        $array = json_decode($data, true);
        return is_array($array) ? $array : [];
    }

    final public function get(string $name)
    {
        if ($name === 'uid') {
            return array_key_exists('uid', $this->current) ? $this->current['uid'] : 0;
        }

        return array_key_exists($name, $this->current) ? $this->current[$name] : null;
    }

    final public function set(string $name, $value): void
    {
        if ($name === 'uid') {
            if (empty($value)) {
                unset($this->current['uid']);
            } else {
                $this->current['uid'] = (int) $value;
            }
            return;
        }

        if (is_null($value)) {
            unset($this->current[$name]);
        } else {
            $this->current[$name] = $value;
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function clear(): void
    {
        $this->current = [];
    }

    public function close(): void
    {
        if (!$this->redis || !$this->id) {
            return;
        }

        $newSession = $this->id !== $this->originalId;
        $newData = $this->current !== $this->original;
        $isUserNow = $this->get('uid') > 0;

        if ($this->current) {
            // update current session
            if ($newSession) {
                $this->redis->set($this->getKey($this->id), self::encodeData($this->current), $isUserNow ? self::ONE_MONTH : self::ONE_DAY);
            } elseif ($newData) {
                $this->redis->set($this->getKey($this->id), self::encodeData($this->current), $this->originalTtl);
            }
        } else {
            // delete current session
            if (!$newSession && $newData) {
                $this->redis->del($this->getKey($this->id));
            }
        }
        $this->redisOnline->set($this->getOnlineKey($this->get('uid'), $this->id), '', self::FIVE_MINUTES);

        $originalUid = array_key_exists('uid', $this->original) ? $this->original['uid'] : 0;
        $isUserBefore = $originalUid > 0;

        if ($isUserBefore && ($newSession || !$isUserNow)) {
            // clean up old user-session mapping
            $this->redis->sRem('u:' . $originalUid, $this->originalId);
        }

        if ($newSession) {
            if ($this->originalId) {
                // clean up old session
                $this->redis->del($this->getKey($this->originalId));
                $this->redisOnline->del($this->getOnlineKey($originalUid, $this->originalId));
            }

            if ($isUserNow) {
                $key = 'u:' . $this->get('uid');

                // clean up expired sessions
                foreach ($this->redis->sMembers($key) as $s) {
                    if (!$this->redis->exists($this->getKey($s))) {
                        $this->redis->sRem($key, $s);
                    }
                }

                // add new user-session mapping
                $this->redis->sAdd($key, $this->id);
                $this->redis->expire($key, self::ONE_MONTH);
            }
        }
    }

    private function getKey(string $sessionId): string
    {
        return 's:' . $sessionId;
    }

    private function getOnlineKey(int $uid, string $sessionId): string
    {
        return 'o:' . $this->cityId . ':' . $uid . ':' . $sessionId;
    }

    public function deleteSessions(int $uid): void
    {
        $key = 'u:' . $uid;
        foreach ($this->redis->sMembers($key) as $s) {
            $this->redis->del($this->getKey($s));
        }

        $this->redis->del($key);
    }

    public function getOnlineUids(): array
    {
        if ($this->redisOnline === null) {
            return [];
        }

        $keys = $this->redisOnline->keys('o:' . $this->cityId . ':*');
        $uids = [];
        foreach ($keys as $k) {
            $f = explode(':', $k);
            $uids[] = (int) $f[2];
        }
        return $uids;
    }
}
