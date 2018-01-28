<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class Node extends DBObject
{
    public $id;
    public $uid;
    public $tid;
    public $createTime;
    public $lastModifiedTime;
    public $title;
    public $viewCount;
    public $weight;
    public $status;

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'nodes', $id, $properties);
    }
}
