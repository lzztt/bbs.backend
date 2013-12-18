<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

/**
 * @property $nid
 * @property $address
 * @property $phone
 * @property $fax
 * @property $email
 * @property $website
 */
class NodeYellowPage extends DBObject
{

   public function __construct( $id = null, $fields = '' )
   {
      $db = DB::getInstance();
      $table = 'node_yellowpages';
      $feilds = [
         'nid' => 'nid',
         'address' => 'address',
         'phone' => 'phone',
         'fax' => 'fax',
         'email' => 'email',
         'website' => 'website'
      ];
      parent::__construct( $db, $table, $feilds, $id, $fields );
   }

}

?>
