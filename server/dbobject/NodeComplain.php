<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class NodeComplain extends DBObject
{
    public $id;
    public $uid;
    public $nid;
    public $reporterUid;
    public $weight;
    public $time;
    public $reason;
    public $status;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'node_complaints';
        parent::__construct($db, $table, $id, $properties);
    }
}
