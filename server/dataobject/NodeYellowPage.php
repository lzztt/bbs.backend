<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dataobject;

use lzx\core\MySQL;
use lzx\core\DataObject;

/**
 * @property $nid
 * @property $address
 * @property $phone
 * @property $fax
 * @property $email
 * @property $website
 */
class NodeYellowPage extends DataObject
{

   public function __construct($load_id = null, $fields = '')
   {
      $db = MySQL::getInstance();
      $table = \array_pop( \explode( '\\', __CLASS__ ) );
      parent::__construct( $db, $table, $load_id, $fields );
   }

}

?>
