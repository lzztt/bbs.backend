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
 * @property $sex
 * @property $age
 * @property $email
 * @property $phone
 * @property $guests
 * @property $time
 * @property $cid
 * @property $status
 */
class FFAttendee extends DBObject
{

   public function __construct( $id = NULL, $fields = '' )
   {
      $db = DB::getInstance();
      $table = 'ff_attendees';
      $feilds = [
         'id' => 'id',
         'name' => 'name',
         'sex' => 'sex',
         'age' => 'age',
         'email' => 'email',
         'phone' => 'phone',
         'guests' => 'guests',
         'time' => 'time',
         'cid' => 'cid',
         'status' => 'status'
      ];
      parent::__construct( $db, $table, $feilds, $id, $fields );
   }

}

//__END_OF_FILE__