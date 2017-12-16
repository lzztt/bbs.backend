<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class Ad extends DBObject
{
    public $id;
    public $name;
    public $typeId;
    public $expTime;
    public $email;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'ads';
        parent::__construct($db, $table, $id, $properties);
    }

    public function getAllAds($from_time = 0)
    {
        return $this->convertColumnNames($this->call('get_ads(' . $from_time . ')'));
    }

    public function getAllAdPayments($from_time = 0)
    {
         return $this->convertColumnNames($this->call('get_ad_payments(' . $from_time . ')'));
    }
}
