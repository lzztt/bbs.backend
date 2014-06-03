<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * two level category system
 * @property $id
 * @property $name
 * @property $email
 * @property $phone
 * @property $guests
 * @property $time
 * @property $status 
 */
class Wedding extends DBObject
{

   public function __construct( $id = null, $properties = '' )
   {
      $db = DB::getInstance();
      $table = 'wedding';
      $fields = [
         'id' => 'id',
         'name' => 'name',
         'email' => 'email',
         'phone' => 'phone',
         'guests' => 'guests',
         'time' => 'time',
         'status' => 'status'
      ];
      parent::__construct( $db, $table, $fields, $id, $properties );
   }

  

}

//__END_OF_FILE__
