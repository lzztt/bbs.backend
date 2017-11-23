<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

class Wedding extends DBObject
{
    public $id;
    public $name;
    public $email;
    public $phone;
    public $guests;
    public $comment;
    public $time;
    public $checkin;
    public $status;
    public $tid;
    public $gift;
    public $value;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'wedding';
        parent::__construct($db, $table, $id, $properties);
    }
}
