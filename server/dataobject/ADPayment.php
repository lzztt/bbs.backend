<?php

namespace site\dataobject;

use lzx\core\DataObject;
use lzx\core\MySQL;

/**
 * @property $id
 * @property $ad_id
 * @property $amount
 * @property $time
 * @property $comment
 */
class ADPayment extends DataObject
{
   public function __construct($load_id = null, $fields = '')
   {
      $db = MySQL::getInstance();
      parent::__construct($db, 'ad_payments', $load_id, $fields);
   }
}


?>
