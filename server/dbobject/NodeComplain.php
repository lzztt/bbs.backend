<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

/**
 * @property $uid
 * @property $nid
 * @property $reporterUID
 * @property $weight
 * @property $time
 * @property $reason
 */
class NodeComplain extends DBObject
{
    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'node_complaints';
        $fields = [
            'id'             => 'id',
            'uid'            => 'uid',
            'nid'            => 'nid',
            'reporterUID' => 'reporter_uid',
            'weight'        => 'weight',
            'time'          => 'time',
            'reason'        => 'reason',
            'status'        => 'status'
        ];
        parent::__construct($db, $table, $fields, $id, $properties);
    }
}

//__END_OF_FILE__
