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

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'ad_payments';
        parent::__construct($db, $table, $id, $properties);
    }
}
