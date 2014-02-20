<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $code
 * @property $time
 * @property $uri
 */
class UserAction extends DBObject
{

   public function __construct( $id = null, $properties = '' )
   {
      $db = DB::getInstance();
      $table = 'user_actions';
      $fields = [
         'id' => 'id',
         'code' => 'code',
         'time' => 'time',
         'uri' => 'uri'
      ];
      parent::__construct( $db, $table, $fields, $id, $properties );
   }

}

//__END_OF_FILE__
