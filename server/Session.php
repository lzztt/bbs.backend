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

    private string $id = '';
    private string $originalId = '';
    private ?Redis $redis = null;
    private ?Redis $redisOnline = null;
    private array $original = [];
    private array $current = [
        'uid' => '0',
        'cid' => '0',
        'data' => [],
    ];


    public function __construct(bool $useDb)
    {
        if (!$useDb) {
            return;
        }

        $this->redis = MemStore::getRedis(MemStore::SESSION);
        $this->redisOnline = MemStore::getRedis(MemStore::ONLINE);
        $this->id = $_COOKIE[self::SID_NAME];

        if (!$this->loadDbSession()) {
            $this->startNewSession();
        }
    }

    private function loadDbSession(): bool
    {
        if (!$this->id) {
            return false;
        }

        $key = $this->getKey($this->id);
        $data = $this->redis->hGetAll($key);
        if (!$data) {
            return false;
        }

        $this->originalId = $this->id;
        $this->original = $data;
        $this->original['data'] = self::decodeData($this->original['data']);

        $this->current = $this->original;

        if ((int) $this->current['uid'] > 0) {
            $ttl = (int) $this->redis->ttl($key);
            if (self::ONE_MONTH - $ttl > self::ONE_DAY) {
                $this->regenerateId();
            }
        }

        return true;
    }

    private function startNewSession(): void
    {
        $this->regenerateId();
    }

    public function regenerateId(): void
    {
        $this->id = bin2hex(random_bytes(8));

        setcookie(self::SID_NAME, $this->id, [
            'expires' => (int) $_SERVER['REQUEST_TIME'] + ((int) $this->current['uid'] > 0
                ? self::ONE_MONTH
                : self::ONE_DAY),
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => false,
            'samesite' => 'Strict'
        ]);
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
        if (in_array($name, ['uid', 'cid'])) {
            return (int) $this->current[$name];
        }

        return array_key_exists($name, $this->current['data']) ? $this->current['data'][$name] : null;
    }

    final public function set(string $name, $value): void
    {
        if (in_array($name, ['uid', 'cid'])) {
            $this->current[$name] = (string) $value;
            return;
        }

        if (is_null($value)) {
            unset($this->current['data'][$name]);
        } else {
            $this->current['data'][$name] = $value;
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function clear(): void
    {
        $this->current['uid'] = '0';
        $this->current['data'] = [];
    }

    public function close(): void
    {
        if ($this->redis === null) {
            return;
        }
        $insert = $this->current;
        $insert['data'] = self::encodeData($this->current['data']);
        $this->redis->hMSet($this->getKey($this->id), $insert);
        $this->redisOnline->set($this->getOnlineKey($this->current['uid'], $this->id), '', self::FIVE_MINUTES);

        $this->updateUserSessionMap();
    }

    private function updateUserSessionMap(): void
    {
        $idChanged = $this->id !== $this->originalId;
        $uidChanged = $this->original && $this->current['uid'] !== $this->original['uid'];
        $isUser = (int) $this->current['uid'] > 0;

        if ($idChanged && $isUser) {
            $key = 'u:' . $this->current['uid'];

            if ($this->originalId) {
                $this->redis->del($this->getKey($this->originalId));
                $this->redis->sRem($key, $this->originalId);
            }

            foreach ($this->redis->sMembers($key) as $s) {
                if (!$this->redis->exists($this->getKey($s))) {
                    $this->redis->sRem($key, $s);
                }
            }
        }

        if ($uidChanged) {
            $this->redisOnline->del($this->getOnlineKey($this->original['uid'], $this->originalId));

            if (!$isUser) {
                $key = 'u:' . $this->original['uid'];
                $this->redis->sRem($key, $this->originalId);
            }
        }

        if ($idChanged || $uidChanged) {
            $this->redis->expire($this->getKey($this->id), $isUser ? self::ONE_MONTH : self::ONE_DAY);

            if ($isUser) {
                $key = 'u:' . $this->current['uid'];
                $this->redis->sAdd($key, $this->id);
                $this->redis->expire($key, self::ONE_MONTH);
            }
        }
    }

    private function getKey(string $sessionId): string
    {
        return 's:' . $sessionId;
    }

    private function getOnlineKey(string $uid, string $sessionId): string
    {
        return 'o:' . $this->current['cid'] . ':' . $uid . ':' . $sessionId;
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

        $keys = $this->redisOnline->keys('o:' . $this->current['cid'] . ':*');
        $uids = [];
        foreach ($keys as $k) {
            $f = explode(':', $k);
            $uids[] = (int) $f[2];
        }
        return $uids;
    }
}
