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

   public function __construct( $id = null, $fields = '' )
   {
      $db = DB::getInstance();
      $table = 'ff_comments';
      $feilds = [
         'id' => 'id',
         'name' => 'name',
         'body' => 'body',
         'time' => 'time'
      ];
      parent::__construct( $db, $table, $feilds, $id, $fields );
   }

}

//__END_OF_FILE__