<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class AdPayment extends DBObject
{
    public $id;
    public $adId;
    public $amount;
    public $time;
    public $comment;

    public function __construct(int $id = 0, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'ad_payments', $id, $properties);
    }
}
