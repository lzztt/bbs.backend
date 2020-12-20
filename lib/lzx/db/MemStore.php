<?php

declare(strict_types=1);

namespace lzx\db;

use Redis;

class MemStore
{
    public const CACHE = 0;
    public const METRICS = 1;
    public const ONLINE = 2;
    public const RATE = 3;
    public const DEDUP = 4;

    public const SESSION = 16;

    // persistent_id=$db, needs INI setting: redis.pconnect.pooling_enabled=0
    public static function getRedis(int $db = self::CACHE): Redis
    {
        static $instances = [];

        if (array_key_exists($db, $instances)) {
            return $instances[$db];
        }

        $redis = new Redis();
        $socket = $db !== self::SESSION
            ? '/run/redis/redis-server.sock'
            : '/run/redis-session/redis-server.sock';

        $redis->pconnect($socket, -1, 0, (string) $db);
        $redis->select($db % 16);

        $instances[$db] = $redis;

        return $redis;
    }
}
