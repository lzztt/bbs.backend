<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $name
 * @property $typeId
 * @property $expTime
 * @property $email
 */
class AD extends DBObject
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
        $fields = [
            'id' => 'id',
            'name' => 'name',
            'typeId' => 'type_id',
            'expTime' => 'exp_time',
            'email' => 'email'
        ];

        return $this->convertFields($this->call('get_ads(' . $from_time . ')'), $fields);
    }

    public function getAllAdPayments($from_time = 0)
    {
         $fields = [
            'id' => 'id',
            'name' => 'name',
            'amount' => 'amount',
            'expTime' => 'exp_time',
            'payTime' => 'pay_time',
            'comment' => 'comment'
         ];
         return $this->convertFields($this->call('get_ad_payments(' . $from_time . ')'), $fields);
    }

    public function getAllAdTypes()
    {
        return $this->call('get_ad_types()');
    }
}
