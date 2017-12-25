<?php declare(strict_types=1);

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

    public function __construct(int $id = 0, string $properties = '')
    {
        $db = DB::getInstance();
        $table = 'comments';
        parent::__construct($db, $table, $id, $properties);
    }
}
