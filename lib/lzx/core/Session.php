<?php

namespace lzx\core;

/*
 * we don't need to set these constants, because we don't do garbage collection here
  define('SESSION_LIFETIME', COOKIE_LIFETIME + 100);
  ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
  ini_set('session.gc_probability', 0);  //set 0, do gc through daily cron job
 */

class Session
{

   protected static $instance;

   const T_NULL = 0;
   const T_DB = 1;

   // CLASS FUNCTIONS

   final public function __get( $key )
   {
      return \array_key_exists( $key, $_SESSION ) ? $_SESSION[$key] : NULL;
   }

   final public function __set( $key, $val )
   {
      $_SESSION[$key] = $val;
   }

   final public function __isset( $key )
   {
      return \array_key_exists( $key, $_SESSION ) ? isset( $_SESSION[$key] ) : FALSE;
   }

   final public function __unset( $key )
   {
      if ( \array_key_exists( $key, $_SESSION ) )
      {
         unset( $_SESSION[$key] );
      }
   }

   final public function clear()
   {
      $_SESSION = array( );
      $this->uid = 0;
   }

   final public static function getInstance()
   {
      if ( !isset( self::$instance ) )
      {
         throw new Exception( 'No Session instance has been set yet' );
      }

      return self::$instance;
   }

   final public static function setInstance( Session $s )
   {
      if ( isset( self::$instance ) )
      {
         throw new Exception( 'Session instance has already been set' );
      }
      self::$instance = $s;
   }

}

//__END_OF_FILE__