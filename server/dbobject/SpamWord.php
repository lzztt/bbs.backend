<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $code
 * @property $time
 * @property $uri
 */
class SpamWord extends DBObject
{

   public function __construct( $id = null, $properties = '' )
   {
      $db = DB::getInstance();
      $table = 'spam_words';
      $fields = [
         'id' => 'id',
         'word' => 'word',
         'lastHitTime' => 'last_hit_time',
      ];
      parent::__construct( $db, $table, $fields, $id, $properties );
   }

}

//__END_OF_FILE__
