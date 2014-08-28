<?php

namespace lzx\cache;

use lzx\cache\CacheHandlerInterface;
use lzx\db\DB;
use lzx\cache\Cache;
use lzx\cache\PageCache;
use lzx\cache\SegmentCache;

class CacheHandler implements CacheHandlerInterface
{

   static public $path;
   protected $_db;

   private function __construct()
   {
      $this->_db = DB::getInstance();
   }

   /**
    * singleton design pattern
    * 
    * @staticvar self $instance
    * @return \lzx\cache\CacheHandler
    */
   public static function getInstance()
   {
      static $instance;

      if ( !isset( $instance ) )
      {
         $instance = new self();
      }
      return $instance;
   }

   /**
    * Factory design patern
    * @return \lzx\core\Cache
    */
   public function createCache( $key )
   {
      return $key[ 0 ] === '/' ? new PageCache( $key ) : new SegmentCache( $key );
   }

   public function getCleanName( $name )
   {
      static $names = [ ];

      if ( \array_key_exists( $name, $names ) )
      {
         return $names[ $name ];
      }

      $_name = \trim( $name );

      if ( \strlen( $_name ) == 0 || \strpos( $_name, ' ' ) !== FALSE )
      {
         throw new \Exception( 'cache name is empty : ' . $name );
      }

      if ( $_name[ 0 ] === '/' )
      {
         // page uri
         if ( !\strpos( $_name, '#' ) )
         {
            // not previously processed
            // use # to seperate uri and query string
            if ( \strpos( $_name, '?' ) )
            {
               // has query string
               $_name = \str_replace( '?', '#', $_name );
            }
            else
            {
               $_name = $_name . '#';
            }
         }
         else
         {
            // previously processed or pre-processed name
            // validate '#'
            if ( \substr_count( $_name, '#' ) > 1 )
            {
               throw new \Exception( 'pre-processed cache name has multiple "#" charactor : ' . $name );
            }

            // validate '?'
            if ( \strpos( $_name, '?' ) )
            {
               throw new \Exception( 'pre-processed cache name has "?" charactor : ' . $name );
            }
         }
      }
      else
      {
         // segment name or event name
         $_name = \preg_replace( '/[^0-9a-z\.\_\-]/i', '_', $_name );
      }

      // save processed name to name cache
      $names[ $name ] = $_name;
      if ( $_name != $name )
      {
         $names[ $_name ] = $_name;
      }

      return $_name;
   }

   public function getFileName( Cache $cache )
   {
      static $_filenames = [ ];

      $key = $cache->getKey();
      if ( \array_key_exists( $key, $_filenames ) )
      {
         return $_filenames[ $key ];
      }

      switch ( \get_class( $cache ) )
      {
         case 'lzx\cache\PageCache':
            $filename = self::$path . '/page' . $key . '.html.gz';
            break;
         case 'lzx\cache\SegmentCache':
            $filename = self::$path . '/segment/' . $key . '.txt';
            break;
         default:
            throw new \Exception( 'unsupport cache type: ' . \get_class( $cache ) );
      }

      $dir = \dirname( $filename );
      if ( !\file_exists( $dir ) )
      {
         \mkdir( $dir, 0755, TRUE );
      }
      $_filenames[ $key ] = $filename;
      return $filename;
   }

   public function getID( $name )
   {
      static $_ids = [ ];
      // found from cached id
      if ( \array_key_exists( $name, $_ids ) )
      {
         return $_ids[ $name ];
      }

      // found from database
      $res = $this->_db->query( 'SELECT id FROM cache_names WHERE name = :key', [ ':key' => $name ] );
      switch ( \count( $res ) )
      {
         case 0:
            // add to database
            $this->_db->query( 'INSERT INTO cache_names (name) VALUEs (:key)', [ ':key' => $name ] );
            // save to id cache
            $id = (int) $this->_db->insert_id();
            break;
         case 1:
            // save to id cache
            $id = (int) \array_pop( $res[ 0 ] );
            break;
         default :
            throw new \Exception( 'multiple ID found for name: ' . $name );
      }
      // save to cache
      $_ids[ $name ] = $id;

      return $id;
   }

   public function unlinkParents( $id )
   {
      $this->_db->query( 'DELETE FROM cache_tree WHERE cid = :cid', [ ':cid' => $id ] );
   }

   public function linkParents( $id, array $parents )
   {
      if ( $parents )
      {
         \array_unique( $parents );

         $existing = \array_column( $this->_db->query( 'SELECT DISTINCT(pid) AS id FROM cache_tree WHERE cid = :cid', [ ':cid' => $id ] ), 'id' );
         $values = [ ];
         foreach ( $parents as $key )
         {
            $pid = $this->getID( $key );
            if ( !\in_array( $pid, $existing ) )
            {
               $values[] = '(' . $pid . ',' . $id . ')';
            }
         }

         if ( $values )
         {
            $this->_db->query( 'INSERT INTO cache_tree VALUES ' . \implode( ',', $values ) );
         }
      }
   }

   public function getChildren( $id )
   {
      $children = $this->_db->query( 'SELECT id, name FROM cache_names WHERE id IN (SELECT DISTINCT(cid) FROM cache_tree WHERE pid = :pid)', [ ':pid' => $id ] );
      foreach ( $children as $c )
      {
         $this->_ids[ $c[ 'name' ] ] = $c[ 'id' ];
      }

      return \array_column( $children, 'name' );
   }

   public function unlinkEvents( $id )
   {
      $this->_db->query( 'DELETE FROM cache_event_listeners WHERE lid = :lid', [ ':lid' => $id ] );
   }

   public function getEventListeners( $eid, $oid )
   {
      $children = $this->_db->query( 'SELECT id, name FROM cache_names WHERE id IN (SELECT DISTINCT(lid) FROM cache_event_listeners WHERE eid = :eid AND oid = :oid)', [ ':eid' => $eid, ':oid' => $oid ] );
      foreach ( $children as $c )
      {
         $this->_ids[ $c[ 'name' ] ] = $c[ 'id' ];
      }

      return \array_column( $children, 'name' );
   }

   public function addEventListeners( $eid, $oid, array $listeners )
   {
      if ( $listeners )
      {
         \array_unique( $listeners );

         $existing = \array_column( $this->_db->query( 'SELECT DISTINCT(lid) AS id FROM cache_event_listeners WHERE eid = :eid AND oid = :oid', [ ':eid' => $eid, ':oid' => $oid ] ), 'id' );
         $values = [ ];
         foreach ( $listeners as $key )
         {
            $lid = $this->getID( $key );
            if ( !\in_array( $lid, $existing ) )
            {
               $values[] = '(' . $eid . ',' . $oid . ',' . $lid . ')';
            }
         }

         if ( $values )
         {
            $this->_db->query( 'INSERT INTO cache_event_listeners VALUES ' . \implode( ',', $values ) );
         }
      }
   }

}

//__END_OF_FILE__
