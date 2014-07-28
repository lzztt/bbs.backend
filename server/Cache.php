<?php

namespace site;

use site\PageCache;
use site\SegmentCache;
use site\CacheHandler;

/**
 * Description of Cache
 *
 * @author ikki
 * @property CacheHandler $_handler
 */
abstract class Cache
{

   static public $path;
   static protected $_handler;
   static protected $_format;
   static protected $_ids = [ ];
   protected $_key;
   protected $_data;
   protected $_class;
   protected $_deleted = FALSE;
   protected $_parents = [ ];
   protected $_events = [ ];
   protected $_id;
   protected $_dirty = FALSE;

   /**
    * Factory design patern
    * @return \site\Cache
    */
   static public function create( $key )
   {
      return $key[ 0 ] === '/' ? new PageCache( $key ) : new SegmentCache( $key );
   }

   static public function setHandler( CacheHandler $handler )
   {
      self::$_handler = $handler;
   }

   public function __construct( $key )
   {
      $this->_key = $this->_getCleanKey( $key );
      $this->_class = \get_class( $this );
   }

   public function getKey()
   {
      return $this->_key;
   }

   public function fetch()
   {
      return $this->_data;
   }

   public function store( $data )
   {
      $this->_data = (string) $data;
      $this->_dirty = TRUE;
   }

   public function delete()
   {
      // clear data
      $this->_data = NULL;
      $this->_dirty = TRUE;
      $this->_deleted = TRUE;
   }

   public function addParent( $key )
   {
      $cleanKey = $this->_getCleanKey( $key );
      if ( $cleanKey && !\in_array( $cleanKey, $this->_parents ) )
      {
         $this->_parents[] = $cleanKey;
      }
      $this->_dirty = TRUE;
   }

   abstract public function flush();

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
            if ( \strpos( $_key, '?' ) )
            {
               // has query string
               $_key = \str_replace( '?', '#', $_key );
            }
            else
            {
               // not previously cleaned
               if ( !\strpos( $_key, '#' ) )
               {
                  $_key = $_key . '#';
               }
            }
         }
         else
         {
            // segment key
            $_key = \preg_replace( '/[^0-9a-z\.\_\-]/i', '_', $_key );
         }

         $keys[ $key ] = $_key;
         return $_key;
      }

      return $keys[ $key ];
   }

   protected function _getFileName()
   {
      static $_filenames = [ ];

      if ( !\array_key_exists( $this->_key, $_filenames ) )
      {
         $type = \strtolower( \str_replace( [ __NAMESPACE__ . '\\', 'Cache' ], '', $this->_class ) );
         $filename = self::$path . '/' . $type . $this->_key . static::$_format;
         $dir = \dirname( $filename );
         if ( !\file_exists( $dir ) )
         {
            \mkdir( $dir, 0755, TRUE );
         }
         $_filenames[ $this->_key ] = $filename;
      }

      return $_filenames[ $this->_key ];
   }

   protected function _deleteDataFile()
   {
      try
      {
         \unlink( $this->_getFileName() );
      }
      catch ( \Exception $e )
      {
         \lzx\core\Logger::getInstance()->error( $e->getMessage() );
      }
   }

   protected function _writeDataFile( $data )
   {
      \file_put_contents( $this->_getFileName(), $data, \LOCK_EX );
   }

   protected function _deleteChildren()
   {
      foreach ( self::$_handler->getChildren( $this->_id ) as $key )
      {
         $cache = self::create( $key );
         $cache->delete();
         $cache->flush();
      }
   }

}

//__END_OF_FILE__
