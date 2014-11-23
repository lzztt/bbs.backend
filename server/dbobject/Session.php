<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $atime
 * @property $uid
 * @property $cid
 * @property $crc
 */
class Session extends DBObject
{

   public function __construct( $id = null, $properties = '' )
   {
      $db = DB::getInstance();
      $table = 'sessions';
      $fields = [
         'id' => 'id',
         'atime' => 'atime',
         'uid' => 'uid',
         'cid' => 'cid',
         'crc' => 'crc'
      ];
      parent::__construct( $db, $table, $fields, $id, $properties );
   }

}

//__END_OF_FILE__
