<?php

namespace lzx\core;

use lzx\core\Logger;

/*
 *
 */

class Handler
{

   private static $errorHandler;
   private static $exceptionHandler;
   public static $logger;
   public static $displayError = TRUE;

   public static function setErrorHandler()
   {
      if ( !isset( self::$errorHandler ) )
      {
         $handler = [__CLASS__, 'errorHandler' ];
         if ( \is_callable( $handler ) )
         {
            \set_error_handler( $handler, \error_reporting() );
            self::$errorHandler = $handler;
         }
         else
         {
            throw new \Exception( 'failed to set error handler' );
         }
      }
   }

   public static function errorHandler( $errno, $errstr, $errfile, $errline )
   {
      throw new \ErrorException( $errstr, 0, $errno, $errfile, $errline );
   }

   public static function setExceptionHandler()
   {
      if ( !isset( self::$exceptionHandler ) )
      {
         $handler = [__CLASS__, 'exceptionHandler' ];
         if ( \is_callable( $handler ) )
         {
            \set_exception_handler( $handler );
            self::$exceptionHandler = $handler;
         }
         else
         {
            throw new \Exception( 'failed to set exception handler' );
         }
      }
   }

   public static function exceptionHandler( \Exception $e )
   {
      $msg = 'Uncaught exception: [' . \get_class( $e ) . '] ' . $e->getMessage();
      if ( self::$logger instanceof Logger )
      {
         self::$logger->error( $msg, $e->getTrace() );
         // flush the log
         self::$logger->flush();
      }
      else
      {
         \error_log( $msg . \PHP_EOL . $e->getTraceAsString() );
      }

      if ( self::$displayError )
      {
         echo $msg . \PHP_EOL;
      }
   }

}

//_END_OF_FILE