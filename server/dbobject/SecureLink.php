<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $uid
 * @property $code
 * @property $time
 * @property $uri
 */
class SecureLink extends DBObject
{

   public function __construct( $id = null, $properties = '' )
   {
      $db = DB::getInstance();
      $table = 'secure_links';
      $fields = [
         'id' => 'id',
         'uid' => 'uid',
         'code' => 'code',
         'time' => 'time',
         'uri' => 'uri'
      ];
      parent::__construct( $db, $table, $fields, $id, $properties );
   }

   public function __toString()
   {
      return $this->uri . '?r=' . $this->id . '&u=' . $this->uid . '&c=' . $this->code . '&t=' . $this->time;
   }

   static public function loadFromRequest( $uri, Array $get )
   {
      $l = new SecureLink();

      if ( isset( $get['r'] ) && isset( $get['u'] ) && isset( $get['c'] ) && isset( $get['t'] ) )
      {
         $l->id = $get['r'];
         $l->uid = $get['u'];
         $l->code = $get['c'];
         $l->time = $get['t'];
         $l->uri = $uri;
         $l->load( 'id' );
      }

      return $l;
   }

}

//__END_OF_FILE__
