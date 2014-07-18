<?php

namespace site;

use lzx\db\DB;

/**
 * Description of Cache
 *
 * @author ikki
 * @property \lzx\db\DB $db
 */
abstract class Cache
{

   static public $status;
   static public $path;
   static protected $format;
   protected $key;
   protected $data;
   protected $isDeleted = FALSE;
   protected $parents = [ ];
   protected $db;

   /**
    * Factory design patern
    */
   static public function getCache( $key )
   {
      return $key[ 0 ] === '/' ? new PageCache( $key ) : new SegmentCache( $key );
   }

   public function __construct( $key )
   {
      $this->key = $this->_getCleanKey( $key );
   }

   public function store( $data )
   {
      $this->data = (string) $data;
   }

   public function delete()
   {
      // clear data
      $this->data = NULL;
      $this->isDeleted = TRUE;
   }

   public function addParent( $key )
   {
      $cleanKey = $this->_getCleanKey( $key );
      if ( !\in_array( $cleanKey, $this->parents ) )
      {
         $this->parents[] = $cleanKey;
      }
   }

   abstract public function flush();

   protected function _rm( $path, $reportError = TRUE )
   {
      try
      {
         if ( !\is_dir( $path ) )
         {
            // remove file
            \unlink( $path );
         }
         else
         {
            // remove all children
            foreach ( \scandir( $path ) as $child )
            {
               if ( $child == '.' || $child == '..' )
               {
                  continue;
               }
               $this->_rm( $path . '/' . $child );
            }

            // remove dir
            \rmdir( $path );
         }
      }
      catch ( \Exception $e )
      {
         if ( $reportError )
         {
            $this->logger->error( $e->getMessage() );
         }
      }
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

      if ( !\array_key_exists( $this->key, $_filenames ) )
      {
         $_filename[ $this->key ] = static::$path . '/' . $this->key . static::$format;
      }

      return $_filename[ $this->key ];
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
      foreach ( $this->_getChildrenKeys() as $k )
      {
         $cache = self::getCache( $k );
         $cache->delete();
         $cache->flush();
      }
   }

   protected function _unlinkParents( $key )
   {
      $this->db->query( 'DELETE FROM cache_tree WHERE cid = (SELECT id FROM cache WHERE name = ' . $this->db->str( $key ) . ')' );
   }

   protected function _saveMap( $pNode, $cNode )
   {
      foreach ( $this->segments as $s )
      {
         $this->_db->query( 'INSERT INTO cache_tree VALUES (:pid, :cid)', $pid, $cid );
      }
   }

}

//__END_OF_FILE__
