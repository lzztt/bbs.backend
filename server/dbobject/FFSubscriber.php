<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $email
 * @property $time
 */
class FFSubscriber extends DBObject
{

   public function __construct($id = null, $fields = '')
   {
      $db = DB::getInstance();
      $table = 'ff_subscribers';
      $feilds = [
         'id' => 'id',
         'email' => 'email',
         'time' => 'time'
      ];
      parent::__construct( $db, $table, $feilds, $id, $fields );
   }

}

//__END_OF_FILE__