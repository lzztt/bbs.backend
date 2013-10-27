<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dataobject;

use lzx\core\DataObject;
use lzx\core\MySQL;

/**
 * @property $rid
 * @property $name
 * @property $description
 * @property $permissions
 */
class Role extends DataObject
{

   public function __construct($load_id = null, $fields = '')
   {
      $db = MySQL::getInstance();
      parent::__construct($db, 'roles', $load_id, $fields);
   }

}
//__END_OF_FILE__

