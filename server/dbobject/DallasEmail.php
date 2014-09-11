<?php

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $email
 * @property $name
 * @property $time
 */
class DallasEmail extends DBObject
{
   public function __construct($id = null, $properties = '')
   {
      $db = DB::getInstance();
      $table = 'dallas_mails';
      $fields = [
         'email' => 'email',
         'name' => 'name',
         'time' => 'time', 
      ];
      parent::__construct( $db, $table, $fields, $id, $properties );
   }
}

//__END_OF_FILE__
