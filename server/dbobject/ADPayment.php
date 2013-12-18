<?php

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $adID
 * @property $amount
 * @property $time
 * @property $comment
 */
class ADPayment extends DBObject
{
   public function __construct($id = null, $fields = '')
   {
      $db = DB::getInstance();
      $table = 'ad_payments';
      $feilds = [
         'id' => 'id',
         'adID' => 'ad_id',
         'amount' => 'amount',
         'time' => 'time',
         'comment' => 'comment'
      ];
      parent::__construct( $db, $table, $feilds, $id, $fields );
   }
}


?>
