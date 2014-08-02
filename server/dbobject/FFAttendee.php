<?php

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $aid
 * @property $name
 * @property $sex
 * @property $age
 * @property $email
 * @property $phone
 * @property $guests
 * @property $time
 * @property $info
 * @property $cid
 * @property $checkin
 * @property $status
 */
class FFAttendee extends DBObject
{

   public function __construct( $id = NULL, $properties = '' )
   {
      $db = DB::getInstance();
      $table = 'ff_attendees';
      $fields = [
         'id' => 'id',
         'aid' => 'aid',
         'name' => 'name',
         'sex' => 'sex',
         'age' => 'age',
         'email' => 'email',
         'phone' => 'phone',
         'guests' => 'guests',
         'time' => 'time',
         'info'=> 'info',
         'cid' => 'cid',
         'checkin' => 'checkin',
         'status' => 'status'
      ];
      parent::__construct( $db, $table, $fields, $id, $properties );
   }
   
}

//__END_OF_FILE__