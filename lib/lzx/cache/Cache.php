<?php

namespace lzx\cache;

use lzx\cache\CacheHandlerInterface;
use lzx\core\Logger;

/**
 * Description of Cache
 *
 * @author ikki
 * @property CacheHandlerInterface $_handler
 */
abstract class Cache
{

   static protected $_handler;
   static protected $_logger;
   static protected $_ids = [ ];
   protected $_key;
   protected $_data;
   protected $_deleted = FALSE;
   protected $_parents = [ ];
   protected $_events = [ ];
   protected $_id;
   protected $_dirty = FALSE;

   static public function setHandler( CacheHandlerInterface $handler )
   {
      self::$_handler = $handler;
   }

   static public function setLogger( Logger $logger )
   {
      self::$_logger = $logger;
   }

   public function __construct( $key )
   {
      $this->_key = self::$_handler->getCleanName( $key );
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
      $cleanKey = self::$_handler->getCleanName( $key );
      if ( $cleanKey && !\in_array( $cleanKey, $this->_parents ) )
      {
         $this->_parents[] = $cleanKey;
      }
      $this->_dirty = TRUE;
   }

   abstract public function flush();

   protected function _deleteDataFile()
   {
      try
      {
         \unlink( self::$_handler->getFileName( $this ) );
      }
      catch ( \Exception $e )
      {
         if ( self::$_logger )
         {
            self::$_logger->warn( $e->getMessage() );
         }
         else
         {
            \error_log( $e->getMessage() );
         }
      }
   }

   protected function _writeDataFile( $data )
   {
      \file_put_contents( self::$_handler->getFileName( $this ), $data, \LOCK_EX );
   }

   protected function _deleteChildren()
   {
      foreach ( self::$_handler->getChildren( $this->_id ) as $key )
      {
         $cache = self::$_handler->createCache( $key );
         $cache->delete();
         $cache->flush();
      }
   }

}

//__END_OF_FILE__
