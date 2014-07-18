<?php

namespace script;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * Description of Fund
 *
 * @property $id
 * @property $name
 * @property $symbol
 * @property $fid
 * @property $cid
 */
class Fund extends DBObject
{

   public function __construct( $id = null, $properties = '' )
   {
      $db = DB::getInstance();
      $table = 'funds';
      $fields = [
         'id' => 'id',
         'symbol' => 'symbol',
         'name' => 'name',
         'fid' => 'fid',
         'cid' => 'cid'
      ];
      parent::__construct( $db, $table, $fields, $id, $properties );
   }

   public function __toString()
   {
      return $this->symbol . ' : ' . $this->name;
   }

}

//__END_OF_FILE__
