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

   private $_userinfo;
   private $_dir;
   private $_file;
   private $_time;
   private $_mailer = NULL;

   private function __construct()
   {
      $this->_file = array(
         self::INFO => 'php_info.log',
         self::DEBUG => 'php_debug.log',
         self::WARNING => 'php_warning.log',
         self::ERROR => 'php_error.log',
      );
      $this->_time = \date( 'Y-m-d H:i:s T', (int) $_SERVER['REQUEST_TIME'] );
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
   public static function getInstance( $setAsDefault = FALSE )
   {
      static $instances = array( );

      $default_key = 'default';

      if ( empty( $logDir ) && \array_key_exists( $default_key, $instances ) )
      {
         return $instances[$default_key];
      }

      if ( !\array_key_exists( $logDir, $instances ) )
      {
         if ( \is_dir( $logDir ) && \is_writable( $logDir ) )
         {
            $instances[$logDir] = new self( $logDir, $logFiles );
            if ( $setAsDefault )
            {
               $instances[$default_key] = $instances[$logDir];
            }
         }
         else
         {
            throw new \InvalidArgumentException( 'logDir is not accessible' );
         }
      }

      return $instances[$logDir];
   }

   public function getLogDir()
   {
      return $this->_dir;
   }

   // only set Dir once
   public function setLogDir( $dir )
   {
      if ( !isset( $this->_dir ) )
      {
         if ( \is_dir( $logDir ) && \is_writable( $logDir ) )
         {
            foreach ( $this->_file as $l => $f )
            {
               $this->_file[$l] = $dir . '/' . $f;
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
         throw new Exception( 'Logger dir has already been set, and could only be set once' );
      }
   }

   public function setUserInfo( $userinfo )
   {
      $this->_userinfo = $userinfo;
   }

   public function setMailer( Mailer $mailer )
   {
      $this->_mailer = $mailer;
   }

   public function info( $str )
   {
      $this->_log( $str, self::INFO, FALSE );
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

   public function error( $str, $trace = TRUE )
   {
      $this->_log( $str, self::ERROR, $trace );
   }

   private function _log( $str, $type, $trace = TRUE, $traces_to_ignore = 2 )
   {
      $log = '[' . $this->_time . '] [' . $this->_userinfo . ']' . \PHP_EOL
            . '[URI] ' . $_SERVER['REQUEST_URI'] . ' <' . $_SERVER['REMOTE_ADDR'] . '> ' . $_SERVER['HTTP_USER_AGENT'] . \PHP_EOL
            . '[' . $type . '] ' . $str . \PHP_EOL;

      if ( $trace )
      {
         $log .= $this->_get_debug_print_backtrace( $traces_to_ignore ) . \PHP_EOL;
      }

      if ( $type == self::ERROR && isset( $this->_mailer ) )
      {
         $this->_mailer->subject = 'web error: ' . $_SERVER['REQUEST_URI'];
         $this->_mailer->body = $log;
         $this->_mailer->send();
      }

      if ( $this->_dir )
      {
         \file_put_contents( $this->_file[$type], $log, \FILE_APPEND | \LOCK_EX );
      }
      else
      {
         \error_log( $log );
      }
   }

   private function _get_debug_print_backtrace( $traces_to_ignore )
   {
      $traces = \debug_backtrace();
      $ret = array( );

      if ( \sizeof( $traces ) > $traces_to_ignore )
      {
         $traces = \array_slice( $traces, $traces_to_ignore );

         foreach ( $traces as $i => $call )
         {
            $object = isset( $call['class'] ) ? $call['class'] . $call['type'] : '';
            $ret[] = '#' . \str_pad( $i, 3, ' ' )
                  . '[' . $object . $call['function'] . ']'
                  . ' called at [' . $call['file'] . ': ' . $call['line'] . ']';
         }
      }

      return \implode( \PHP_EOL, $ret );
   }

}

//__END_OF_FILE__
