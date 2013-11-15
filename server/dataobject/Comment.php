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
      $table = \array_pop( \explode( '\\', __CLASS__ ) );
      parent::__construct( $db, $table, $load_id, $fields );
   }

   public function delete()
   {
      $this->_db->query( 'INSERT INTO ImageDeleted (fid, path) SELECT fid, path FROM Image AS f WHERE f.cid = ' . $this->cid );
      $this->_db->query( 'DELETE c, f FROM Comment AS c LEFT JOIN Image AS f ON c.cid = f.cid WHERE c.cid = ' . $this->cid );
      /*
        if (\is_null($this->uid))
        {
        $this->load('uid');
        }
        if (isset($this->uid))
        {
        $this->_db->query('UPDATE User SET points = points - 1 WHERE uid = ' . $this->uid);
        }
       */
   }

   public function getHash()
   {
      return \md5( $this->uid . '_' . $this->body, TRUE );
   }

   public function add()
   {
      // CHECK USER
      $userInfo = $this->_db->row( 'SELECT createTime, lastAccessIPInt, status FROM User WHERE uid = ' . $this->uid );
      if ( \intval( $userInfo['status'] ) != 1 )
      {
         throw new \Exception( 'This User account cannot post message.' );
      }
      $days = \intval( ($this->createTime - $userInfo['createTime']) / 86400 );
      // registered less than 1 month
      if ( $days < 30 )
      {
         $geo = \geoip_record_by_name( \long2ip( $userInfo['lastAccessIPInt'] ) );
         // not from Texas
         if ( !$geo || $geo['region'] != 'TX' )
         {
            //select (select count(*) from Node where uid = 126 and createTime > unix_timestamp() - 86400) + ( select count(*) from Comment where uid = 126 and createTime > unix_timestamp() - 86400) AS count
            $oneday = \intval( $this->createTime - 86400 );
            $count = $this->_db->val(
                  'SELECT 
                  ( SELECT count(*) FROM Node WHERE uid = ' . $this->uid . ' AND createTime > ' . $oneday . ' ) +
                  ( SELECT count(*) FROM Comment WHERE uid = ' . $this->uid . ' AND createTime > ' . $oneday . ' ) AS c'
            );
            if ( $count >= $days )
            {
               throw new \Exception( 'Quota limitation reached!<br />Your account is ' . $days . ' days old, so you can only post ' . $days . ' messages within 24 hours. <br /> You already have ' . $count . ' message posted in last 24 hours. Please wait for several hours to get more quota.' );
            }
         }
      }

      // check spam
      $nodes = $this->_db->select( 'SELECT createTime FROM ' . $this->_table . ' WHERE uid = ' . $this->uid . ' AND createTime > ' . (\intval( $this->createTime ) - 600) . ' ORDER BY createTime DESC' );

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

      $count = $this->_db->val( 'SELECT count(*) FROM ' . $this->_table . ' WHERE hash = ' . $this->_db->str( $this->hash ) . ' AND createTime > ' . (\intval( $this->createTime ) - 30) );
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
         $count = $this->_db->val( 'SELECT count(*) FROM ' . $this->_table . ' WHERE hash = ' . $this->_db->str( $this->hash ) . ' AND createTime > ' . (\intval( $this->lastModifiedTime ) - 30) . ' AND cid != ' . $this->cid );
         if ( $count > 0 )
         {
            throw new \Exception( 'duplicate comment found' );
         }
      }

      parent::update( $fields );
   }

}

//__END_OF_FILE__
