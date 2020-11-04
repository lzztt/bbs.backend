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
    public $reporterUid;
    public $weight;
    public $time;
    public $reason;
    public $status;

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'node_complaints', $id, $properties);
    }
}
