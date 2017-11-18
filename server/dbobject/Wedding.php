<?php declare(strict_types=1);

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * two level category system
 * @property $id
 * @property $name
 * @property $email
 * @property $phone
 * @property $guests
 * @property $time
 * @property $checkin
 * @property $status
 * @property $table
 */
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

    public function getTotal()
    {
        $arr = $this->db->query('SELECT SUM(guests) FROM wedding');
        return array_pop($arr[0]);
    }
}
