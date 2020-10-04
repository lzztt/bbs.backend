<?php declare(strict_types=1);

namespace site;

use Redis;
use lzx\db\MemStore;

class Session
{
    private const SID_NAME = 'LZXSID';
    private const JSON_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    private const DEFAULT_DATA = [
        'uid' => '0',
        'cid' => '0',
        'data' => [],
    ];

    private string $id = '';
    private ?Redis $redis = null;
    private ?Redis $redisOnline = null;
    private int $time;
    private array $current = [];
    private array $original = [];

    public static function getInstance(bool $useDb = true): Session
    {
        static $instance;

        if (!$instance) {
            $instance = new self($useDb);
        }

        return $instance;
    }

    private function __construct(bool $useDb)
    {
        $this->time = (int) $_SERVER['REQUEST_TIME'];
        if (!$useDb) {
            $this->current = self::DEFAULT_DATA;
            return;
        }

        $this->redis = MemStore::getRedis(1);
        $this->redisOnline = MemStore::getRedis(2);

        $this->id = empty($_COOKIE[self::SID_NAME]) ? '' : $_COOKIE[self::SID_NAME];

        if (!$this->loadDbSession()) {
            $this->startNewSession();
        }
        // remove old version cookie with domain wildcard
        setcookie(self::SID_NAME, $this->id, $this->time - 2592000, '/', '.' . implode('.', array_slice(explode('.', $_SERVER['SERVER_NAME']), -2)));
    }

    private function loadDbSession(): bool
    {
        if (!$this->id) {
            return false;
        }

        $data = $this->redis->hGetAll('s:' . $this->id);
        if (!$data) {
            return false;
        }

        $this->original = $data;
        $this->original['data'] = self::decodeData($this->original['data']);

        $this->current = $this->original;

        return true;
    }

    private function startNewSession(): void
    {
        $this->id = bin2hex(random_bytes(8));
        $this->current = self::DEFAULT_DATA;

        setcookie(self::SID_NAME, $this->id, $this->time + 2592000, '/');
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
        if (!$this->id) {
            return;
        }
        $this->updateDbSession();
    }

    private function updateDbSession(): void
    {
        $insert = $this->current;
        $insert['data'] = self::encodeData($this->current['data']);
        $this->redis->hMSet('s:' . $this->id, $insert);
        $this->redis->expire('s:' . $this->id, (int) $this->current['uid'] > 0 ? 2592000 : 86400);

        if ($this->original && $this->current['uid'] != $this->original['uid']) {
            $this->redisOnline->del($this->getTimeKey($this->original['uid']));
        }
        $this->redisOnline->set($this->getTimeKey($this->current['uid']), '', 300);
    }

    private function getTimeKey(string $uid): string
    {
        return 'o:' . $this->current['cid'] . ':' . $uid . ':' . $this->id;
    }

    public function deleteSession(string $id): void
    {
        if ($this->redis) {
            $this->redis->del($id);
        }
    }

    public function getOnlineUids(): array
    {
        if (empty($this->redisOnline)) {
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
