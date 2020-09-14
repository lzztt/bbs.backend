<?php declare(strict_types=1);

namespace lzx\db;

use Redis;

class MemStore
{
    // persistent_id=$db, needs INI setting: redis.pconnect.pooling_enabled=0
    public static function getRedis(int $db = 0): Redis
    {
        static $instances = [];

        $id = (string) $db;
        if (array_key_exists($id, $instances)) {
            return $instances[$id];
        }

        $redis = new Redis();
        $redis->pconnect('/run/redis/redis-server.sock', -1, 0, $id);
        $redis->select($db);

        $instances[$id] = $redis;

        return $redis;
    }
}
