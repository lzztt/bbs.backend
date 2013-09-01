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
 * @property $hash
 * @property $createTime
 * @property $lastModifiedTime
 */
class Comment extends DataObject
{

   public function __construct( $load_id = null, $fields = '' )
   {
      $db = MySQL::getInstance();

      parent::__construct( $db, 'comments', $load_id, $fields );
   }

   public function delete()
   {
      $this->_db->query( 'INSERT INTO files_deleted (fid, path) SELECT fid, path FROM files AS f WHERE f.cid = ' . $this->cid );
      $this->_db->query( 'DELETE c, f FROM comments AS c LEFT JOIN files AS f ON c.cid = f.cid WHERE c.cid = ' . $this->cid );
      /*
        if (\is_null($this->uid))
        {
        $this->load('uid');
        }
        if (isset($this->uid))
        {
        $this->_db->query('UPDATE users SET points = points - 1 WHERE uid = ' . $this->uid);
        }
       */
   }

   public function getHash()
   {
      return \md5( $this->uid . '_' . $this->body, TRUE );
   }

   public function add()
   {
      // check spam
      $nodes = $this->_db->select( 'select createTime from ' . $this->_table . ' where uid = ' . $this->uid . ' and createTime > ' . (\intval( $this->createTime ) - 600) . ' order by createTime DESC' );

      $count = \sizeof( $nodes );
      if ( $count > 0 )
      {
         // limit 1 comments within 10 seconds
         if ( $this->createTime - \intval( $nodes[0]['createTime'] ) < 10 )
         {
            throw new \Exception( 'You are posting too fast. Please slow down.' );
         }
      }

      if ( $count > 29 )
      {
         // limit 30 comments within 10 minutes
         throw new \Exception( 'too many comments posted within 10 minutes' );
      }

      // check duplicate
      $this->hash = $this->getHash();

      $count = $this->_db->val( 'select count(*) from ' . $this->_table . ' where hash = ' . $this->_db->str( $this->hash ) . ' and createTime > ' . (\intval( $this->createTime ) - 30) );
      if ( $count > 0 )
      {
         throw new \Exception( 'duplicate comment found' );
      }

      parent::add();
   }

   public function update( $fields = '' )
   {
      if ( $fields == '' )
      {
         $this->hash = $this->getHash();
      }
      else
      {
         $f = \explode( ',', $fields );
         if ( \in_array( 'body', $f ) )
         {
            $this->hash = $this->getHash();
            if ( !\in_array( 'hash', $f ) )
            {
               $fields .=',hash';
            }
         }
      }

      if ( isset( $this->hash ) )
      {
         $count = $this->_db->val( 'select count(*) from ' . $this->_table . ' where hash = ' . $this->_db->str( $this->hash ) . ' and createTime > ' . (\intval( $this->lastModifiedTime ) - 30) . ' and cid != ' . $this->cid );
         if ( $count > 0 )
         {
            throw new \Exception( 'duplicate comment found' );
         }
      }

      parent::update( $fields );
   }

}

//__END_OF_FILE__
