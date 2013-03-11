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

      parent::__construct($db, 'node_yellow_pages', $load_id, $fields);
   }

}

?>
