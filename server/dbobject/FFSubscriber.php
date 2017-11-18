<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

class FFSubscriber extends DBObject
{
    public $id;
    public $email;
    public $time;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'ff_subscribers';
        parent::__construct($db, $table, $id, $properties);
    }
}
