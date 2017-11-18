<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

class FFComment extends DBObject
{
    public $id;
    public $aid;
    public $name;
    public $body;
    public $time;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'ff_comments';
        parent::__construct($db, $table, $id, $properties);
    }
}
