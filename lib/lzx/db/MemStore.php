<?php

declare(strict_types=1);

namespace lzx\db;

use Redis;

class MemStore
{
    public const CACHE = 0;
    public const SESSION = 1;
    public const ONLINE = 2;
    public const RATE = 3;

    // persistent_id=$db, needs INI setting: redis.pconnect.pooling_enabled=0
    public static function getRedis(int $db = self::CACHE): Redis
    {
        static $instances = [];

        $id = (string) $db;
        if (array_key_exists($id, $instances)) {
            return $instances[$id];
        }

        $redis = new Redis();
        if ($db !== self::SESSION) {
            $redis->pconnect('/run/redis/redis-server.sock', -1, 0, $id);
            $redis->select($db);
        } else {
            $redis->pconnect('/run/redis-session/redis-server.sock', -1, 0, '0');
        }

        $instances[$id] = $redis;

        return $redis;
    }
}
