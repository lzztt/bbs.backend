<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

class FFAttendee extends DBObject
{
    public $id;
    public $aid;
    public $name;
    public $sex;
    public $age;
    public $email;
    public $phone;
    public $guests;
    public $time;
    public $info;
    public $cid;
    public $checkin;
    public $status;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'ff_attendees';
        parent::__construct($db, $table, $id, $properties);
    }
}
