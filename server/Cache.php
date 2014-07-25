<?php

namespace site;

use lzx\db\DB;

/**
 * Description of Cache
 *
 * @author ikki
 * @property \lzx\db\DB $_db
 */
abstract class Cache
{

   static public $status;
   static public $path;
   static protected $_format;
   static protected $_ids = [ ];
   protected $_key;
   protected $_data;
   protected $_isDeleted = FALSE;
   protected $_parents = [ ];
   protected $_db;
   protected $_id;

   /**
    * Factory design patern
    */
   static protected function _getCache( $key )
   {
      return $key[ 0 ] === '/' ? new PageCache( $key ) : new SegmentCache( $key );
   }

   public function __construct( $key )
   {
      $this->_key = $this->_getCleanKey( $key );
   }

   public function getKey()
   {
      return $this->_key;
   }

   public function store( $data )
   {
      $this->_data = (string) $data;
   }

   public function delete()
   {
      // clear data
      $this->_data = NULL;
      $this->_isDeleted = TRUE;
   }

   public function addParent( $key )
   {
      $cleanKey = $this->_getCleanKey( $key );
      if ( $cleanKey && !\in_array( $cleanKey, $this->_parents ) )
      {
         $this->_parents[] = $cleanKey;
      }
   }

   abstract public function flush();

   protected function _initDB()
   {
      if ( !$this->_db )
      {
         $this->_db = DB::getInstance();
      }
   }

   protected function _getID( $key = NULL )
   {
      if ( !$key )
      {
         $key = $this->_key;
      }

      // found from cached id
      if ( \array_key_exists( $key, self::$_ids ) )
      {
         return self::$_ids[ $key ];
      }

      // found from database
      $res = $this->_db->query( 'SELECT id FROM cache WHERE `key` = :key', [ ':key' => $key ] );
      switch ( \count( $res ) )
      {
         case 0:
            // add to database
            $this->_db->query( 'INSERT INTO cache (`key`) VALUEs (:key)', [ ':key' => $key ] );
            // save to id cache
            self::$_ids[ $key ] = (int) $this->_db->insert_id();
            break;
         case 1:
            // save to id cache
            self::$_ids[ $key ] = (int) \array_pop( $res[ 0 ] );
            break;
         default :
            throw new \Exception( 'SELECT Key error' );
      }

      return self::$_ids[ $key ];
   }

   protected function _getCleanKey( $key )
   {
      static $keys = [ ];

      if ( !\array_key_exists( $key, $keys ) )
      {
         $_key = \trim( $key );

         if ( \strlen( $_key ) == 0 || \strpos( $_key, ' ' ) !== FALSE )
         {
            throw new \Exception( 'error cache key : ' . $key );
         }

         if ( $_key[ 0 ] === '/' )
         {
            // page uri
            $keys[ $key ] = \strpos( $_key, '?' ) ? \str_replace( '?', '#', $_key ) : ($_key . '#');
         }
         else
         {
            // segment key
            $keys[ $key ] = \preg_replace( '/[^0-9a-z\.\_\-]/i', '_', $_key );
         }
      }

      return $keys[ $key ];
   }

   protected function _getFileName()
   {
      static $_filenames = [ ];

      if ( !\array_key_exists( $this->_key, $_filenames ) )
      {
         $_filename[ $this->_key ] = static::$path . '/' . $this->_key . static::$_format;
      }

      return $_filename[ $this->_key ];
   }

   protected function _deleteDataFile()
   {
      try
      {
         \unlink( $this->_getFileName() );
      }
      catch ( \Exception $e )
      {
         // ignore exception
      }
   }

   protected function _writeDataFile( $data )
   {
      \file_put_contents( $this->_getFileName(), $data, \LOCK_EX );
   }

   protected function _deleteChildren()
   {
      if ( $this->_id )
      {
         $children = $this->_db->query( 'SELECT id, `key` FROM cache WHERE id IN (SELECT cid FROM cache_tree WHERE pid = :pid)', [ ':pid' => $this->_id ] );
         foreach ( $children as $c )
         {
            // cache ids
            self::$_ids[ $c[ 'key' ] ] = $c[ 'id' ];

            $cache = self::_getCache( $c[ 'key' ] );
            $cache->delete();
            $cache->flush();
         }
      }
   }

   protected function _unlinkParents()
   {
      if ( $this->_id )
      {
         $this->_db->query( 'DELETE FROM cache_tree WHERE cid = :cid', [ ':cid' => $this->_id ] );
      }
   }

   // TODO
   protected function _linkParents()
   {
      if ( $this->_parents )
      {
         \array_unique( $this->_parents );

         $ids = [ ];
         foreach ( $this->_parents as $key )
         {
            $ids[] = $this->_getID( $key );
         }

         $values = [ ];
         foreach ( $ids as $pid )
         {
            $values[] = '(' . $pid . ',' . $this->_id . ')';
         }

         $this->_db->query( 'INSERT INTO cache_tree VALUES ' . \implode( ',', $values ) );
      }
   }

}

//__END_OF_FILE__
