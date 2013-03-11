<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dataobject;

use lzx\core\DataObject;
use lzx\core\MySQL;

/**
 * @property $cid
 * @property $name
 * @property $body
 * @property $time
 */
class FFComment extends DataObject
{

   public function __construct($load_id = null, $fields = '')
   {
      $db = MySQL::getInstance();
      parent::__construct($db, 'fyfm_comments', $load_id, $fields);
   }

}

//__END_OF_FILE__