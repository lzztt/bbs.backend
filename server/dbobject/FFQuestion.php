<?php

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $aid
 * @property $body
 */
class FFQuestion extends DBObject
{

   public function __construct( $id = NULL, $properties = '' )
   {
      $db = DB::getInstance();
      $table = 'ff_questions';
      $fields = [
         'id' => 'id',
         'aid' => 'aid',
         'body' => 'body'
      ];

      parent::__construct( $db, $table, $fields, $id, $properties );
   }

}

//__END_OF_FILE__