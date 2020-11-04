<?php

declare(strict_types=1);

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

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'ads', $id, $properties);
    }

    public function getAllAds(int $from_time = 0): array
    {
        return $this->convertColumnNames($this->call('get_ads(' . $from_time . ')'));
    }

    public function getAllAdPayments(int $from_time = 0): array
    {
        return $this->convertColumnNames($this->call('get_ad_payments(' . $from_time . ')'));
    }
}
