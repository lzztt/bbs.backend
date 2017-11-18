<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

class Comment extends DBObject
{
    public $id;
    public $nid;
    public $uid;
    public $tid;
    public $body;
    public $createTime;
    public $lastModifiedTime;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'comments';
        parent::__construct($db, $table, $id, $properties);
    }
}
