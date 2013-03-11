<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dataobject;

use lzx\core\DataObject;
use lzx\core\MySQL;

/**
 * @property $cid
 * @property $nid
 * @property $uid
 * @property $body
 * @property $createTime
 * @property $lastModifiedTime
 */
class Comment extends DataObject
{

   public function __construct($load_id = null, $fields = '')
   {
      $db = MySQL::getInstance();

      parent::__construct($db, 'comments', $load_id, $fields);
   }

   public function delete()
   {
      $this->_db->query('INSERT INTO files_deleted (fid, path) SELECT fid, path FROM files AS f WHERE f.cid = ' . $this->cid);
      $this->_db->query('DELETE c, f FROM comments AS c LEFT JOIN files AS f ON c.cid = f.cid WHERE c.cid = ' . $this->cid);
      if (\is_null($this->uid))
      {
         $this->load('uid');
      }
      if (isset($this->uid))
      {
         $this->_db->query('UPDATE users SET points = points - 1 WHERE uid = ' . $this->uid);
      }
   }

}

//__END_OF_FILE__
