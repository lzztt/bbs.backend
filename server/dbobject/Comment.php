<?php

declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class Comment extends DBObject
{
    public $id;
    public $nid;
    public $uid;
    public $tid;
    public $body;
    public $createTime;
    public $lastModifiedTime;
    public $status;

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'comments', $id, $properties);
    }
}
