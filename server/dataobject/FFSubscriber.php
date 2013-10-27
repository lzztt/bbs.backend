<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dataobject;

use lzx\core\DataObject;
use lzx\core\MySQL;

/**
 * @property $sid
 * @property $email
 * @property $time
 */
class FFSubscriber extends DataObject
{

   public function __construct($load_id = null, $fields = '')
   {
      $db = MySQL::getInstance();
      parent::__construct($db, 'fyfm_subscribers', $load_id, $fields);
   }

}

//__END_OF_FILE__