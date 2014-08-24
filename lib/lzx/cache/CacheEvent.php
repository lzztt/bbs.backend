<?php

namespace lzx\cache;

use lzx\cache\Cache;
use lzx\cache\CacheHandlerInterface;

/**
 * @property Cache[] $_listeners
 * @property CacheHandler $_handler
 */
class CacheEvent
{

   static protected $_handler;
   protected $_id;
   protected $_name;
   protected $_listeners = [ ];
   protected $_dirty = FALSE;
   protected $_triggered = FALSE;

   static public function setHandler( CacheHandlerInterface $handler )
   {
      self::$_handler = $handler;
   }

   public function __construct( $name )
   {
      $this->_name = self::$_handler->getCleanName( $name );
   }
   
   public function getName()
   {
      return $this->_name;
   }

   /**
    * 
    * add a listener to an event
    */
   public function addListener( Cache $c )
   {
      if ( $c )
      {
         if ( !\in_array( $c->getKey(), $this->_listeners ) )
         {
            $this->_listeners[] = $c->getKey();
         }
         $this->_dirty = TRUE;
      }
   }

   /**
    * trigger an event.
    * This will delete and unlink all its listeners
    */
   public function trigger()
   {
      $this->_triggered = TRUE;
      $this->_dirty = TRUE;
   }

   public function flush()
   {
      if ( $this->_dirty )
      {
         $this->_id = self::$_handler->getID( $this->_name );

         if ( $this->_triggered )
         {
            // update current listeners
            foreach ( $this->_listeners as $key )
            {
               $c = self::$_handler->createCache( $key );
               $c->delete();
               $c->flush();
            }
            // clear current listeners
            $this->_listeners = [ ];

            // update listeners in DB
            foreach ( self::$_handler->getChildren( $this->_id ) as $key )
            {
               $c = self::$_handler->createCache( $key );
               $c->delete();
               $c->flush();
            }
         }
         else
         {
            self::$_handler->linkChildren( $this->_id, $this->_listeners );
         }
         $this->_dirty = FALSE;
      }
   }

}

//__END_OF_FILE__
