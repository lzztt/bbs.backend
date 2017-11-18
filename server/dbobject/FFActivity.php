<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

class FFActivity extends DBObject
{
    public $id;
    public $name;
    public $time;
    public $nid;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'ff_activities';
        parent::__construct($db, $table, $id, $properties);
    }
}
