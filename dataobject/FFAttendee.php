<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dataobject;

use lzx\core\DataObject;
use lzx\core\MySQL;

/**
 * @property $aid
 * @property $name
 * @property $sex
 * @property $age
 * @property $email
 * @property $phone
 * @property $guests
 * @property $time
 * @property $cid
 */
class FFAttendee extends DataObject
{

   public function __construct($load_id = NULL, $fields = '')
   {
     $db = MySQL::getInstance();
      parent::__construct($db, 'fyfm_attendees', $load_id, $fields);
   }

}

//__END_OF_FILE__