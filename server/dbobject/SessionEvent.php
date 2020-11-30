<?php

declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class SessionEvent extends DBObject
{
    public $id;
    public $userId;
    public $time;
    public $ip;
    public $agent;
    public $hash;
    public $count;

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'session_events', $id, $properties);
    }

    public function getIps(array $uids): array
    {
        if (!$uids) {
            return [];
        }

        return $this->db->query('
        WITH
        user_ip AS (
            SELECT user_id, ip, MAX(time) AS time
            FROM session_events
            WHERE user_id IN (' . implode(',', $uids) . ')
            GROUP BY user_id, ip),
        ip_rank AS (
            SELECT user_id, ip,
                ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY time DESC) AS rn
            FROM user_ip)
        SELECT user_id, ip FROM ip_rank WHERE rn < 4;');
    }
}
