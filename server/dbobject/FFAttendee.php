<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $aid
 * @property $name
 * @property $sex
 * @property $age
 * @property $email
 * @property $phone
 * @property $guests
 * @property $time
 * @property $info
 * @property $cid
 * @property $checkin
 * @property $status
 */
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
