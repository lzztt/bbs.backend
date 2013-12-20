<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $name
 * @property $body
 * @property $time
 */
class FFComment extends DBObject
{

   public function __construct( $id = null, $properties = '' )
   {
      $db = DB::getInstance();
      $table = 'ff_comments';
      $fields = [
         'id' => 'id',
         'name' => 'name',
         'body' => 'body',
         'time' => 'time'
      ];
      parent::__construct( $db, $table, $fields, $id, $properties );
   }

}

//__END_OF_FILE__