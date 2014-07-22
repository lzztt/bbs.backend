<?php

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $name
 * @property $time
 * @property $nid
 */
class FFActivity extends DBObject
{

   public function __construct( $id = NULL, $properties = '' )
   {
      $db = DB::getInstance();
      $table = 'ff_activities';
      $fields = [
         'id' => 'id',
         'name' => 'name',
         'time' => 'time',
         'nid' => 'nid'
      ];
      parent::__construct( $db, $table, $fields, $id, $properties );
   }

}

//__END_OF_FILE__
