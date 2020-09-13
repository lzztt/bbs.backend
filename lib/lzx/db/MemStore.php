<?php declare(strict_types=1);

namespace lzx\db;

use Redis;

class MemStore
{
    private static ?Redis $redis = null;

    public static function getRedis(): Redis
    {
        if (!self::$redis) {
            self::$redis = new Redis();
            self::$redis->pconnect('/run/redis/redis-server.sock');
        }

        return self::$redis;
    }
}
