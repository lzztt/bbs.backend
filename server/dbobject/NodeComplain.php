<?php

declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class NodeComplain extends DBObject
{
    public $id;
    public $uid;
    public $nid;
    public $cid;
    public $reporterUid;
    public $weight;
    public $time;
    public $reason;
    public $status;

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'node_complaints', $id, $properties);
    }

    public function getCommentComplains(array $cids): array
    {
        if (!$cids) {
            return [];
        }

        return $this->db->query('
            SELECT uid, cid, MAX(status) AS status, MAX(time) AS lastReportTime, (
                SELECT COUNT(DISTINCT cid)
                FROM node_complaints
                WHERE status = 2 AND uid =  nc.uid) AS reportCount
            FROM node_complaints AS nc
            WHERE cid IN (' . implode(',', $cids) . ')
            GROUP BY cid;');
    }

    public function getViolationCount(int $uid): int
    {
        return (int) array_pop(array_pop($this->db->query('
            SELECT COUNT(DISTINCT cid)
            FROM node_complaints
            WHERE status = 2 AND uid = ' . $uid . ';')));
    }
}
