<?php

namespace lzx\core;

use lzx\core\Mailer;

/**
 * @param Mailer $_mailer
 */
class Logger
{// static class instead of singleton

   const INFO = 'INFO';
   const DEBUG = 'DEBUG';
   const WARNING = 'WARNING';
   const ERROR = 'ERROR';

   private $_userinfo = [ ];
   private $_dir;
   private $_file;
   private $_time;
   private $_mailer = NULL;
   private $_logCache;

   private function __construct()
   {
      $this->_file = [
         self::INFO => 'php_info.log',
         self::DEBUG => 'php_debug.log',
         self::WARNING => 'php_warning.log',
         self::ERROR => 'php_error.log'
      ];
      // initialize cache
      $this->_logCache = [
         self::INFO => '',
         self::DEBUG => '',
         self::WARNING => '',
         self::ERROR => ''
      ];
      $this->_time = \date( 'Y-m-d H:i:s T', (int) $_SERVER[ 'REQUEST_TIME' ] );
   }

   public function __destruct()
   {
      $this->flush();
   }

   /**
    * 
    * @staticvar array $instances
    * @param type $logDir
    * @param array $logFiles
    * @param type $setAsDefault
    * @return Logger
    * @throws \InvalidArgumentException
    */
   public static function getInstance()
   {
      static $instance;

      if ( \is_null( $instance ) )
      {
         $instance = new self();
      }

      return $instance;
   }

   // only set Dir once
   public function setDir( $dir )
   {
      if ( !isset( $this->_dir ) )
      {
         if ( \is_dir( $dir ) && \is_writable( $dir ) )
         {
            foreach ( $this->_file as $l => $f )
            {
               $this->_file[ $l ] = $dir . '/' . $f;
            }
            $this->_dir = $dir;
         }
         else
         {
            throw new \InvalidArgumentException( 'Log dir is not an readable directory : ' . $dir );
         }
      }
      else
      {
         throw new \Exception( 'Logger dir has already been set, and could only be set once' );
      }
   }

   public function setEmail( $email )
   {
      if ( \filter_var( $email, \FILTER_VALIDATE_EMAIL ) )
      {
         $this->_mailer = new Mailer( 'logger' );
         $this->_mailer->to = $email;
      }
      else
      {
         throw new \Exception( 'Invalid email address: ' . $email );
      }
   }

   public function setUserInfo( array $userinfo )
   {
      $this->_userinfo = $userinfo;
   }

   public function info( $str )
   {
      $this->_log( $str, self::INFO );
   }

   public function debug( $var )
   {
      \ob_start();
      \var_dump( $var );
      $str = \ob_get_contents();   // Get the contents of the buffer
      \ob_end_clean();

      $this->_log( \trim( $str ), self::DEBUG );
   }

   public function warn( $str )
   {
      $this->_log( $str, self::WARNING );
   }

   public function error( $str, array $traces = [ ] )
   {
      $this->_log( $str, self::ERROR, $traces );
   }

   public function flush()
   {
      if ( $this->_dir )
      {
         foreach ( $this->_logCache as $type => $log )
         {
            if ( $log )
            {
               \file_put_contents( $this->_file[ $type ], $log, \FILE_APPEND | \LOCK_EX );
            }
         }
      }
      else
      {
         \error_log( \implode( '', $this->_logCache ) );
      }

      // clear the cache
      $this->_logCache = [
         self::INFO => '',
         self::DEBUG => '',
         self::WARNING => '',
         self::ERROR => ''
      ];
   }

   private function _log( $str, $type, array $traces = NULL )
   {
      $log = ['time' => $this->_time, 'type' => $type ];
      foreach ( $this->_userinfo as $k => $v )
      {
         $log[ $k ] = $v;
      }
      $log[ 'uri' ] = $_SERVER[ 'REQUEST_URI' ];
      $log[ 'client' ] = $_SERVER[ 'REMOTE_ADDR' ] . ' : ' . $_SERVER[ 'HTTP_USER_AGENT' ];
      $log[ 'message' ] = $str;

      if ( $traces !== NULL )
      {
         $log[ 'trace' ] = $this->_get_debug_print_backtrace( $traces );
      }

      if ( $type == self::ERROR && isset( $this->_mailer ) )
      {
         $this->_mailer->subject = 'web error: ' . $_SERVER[ 'REQUEST_URI' ];
         $this->_mailer->body = \json_encode( $log, \JSON_NUMERIC_CHECK | \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE );
         $this->_mailer->send();
         $log[ '_SERVER' ] = $_SERVER;
      }

      $this->_logCache[ $type ] .= \json_encode( $log, \JSON_NUMERIC_CHECK | \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE ) . \PHP_EOL;
   }

   private function _get_debug_print_backtrace( array $traces )
   {
      if ( empty( $traces ) )
      {
         $traces = \array_slice( \debug_backtrace(), 2 );
      }
      $ret = [ ];

      foreach ( $traces as $i => $call )
      {
         $ret[] = '#' . \str_pad( $i, 3, ' ' )
            . ($call[ 'class' ] ? $call[ 'class' ] . $call[ 'type' ] . $call[ 'function' ] : $call[ 'function' ])
            . ' @ ' . $call[ 'file' ] . ':' . $call[ 'line' ];
      }

      return $ret;
   }

}

//__END_OF_FILE__    