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
    public static $displayError = True;

    public static function setErrorHandler()
    {
        if ( !isset( self::$errorHandler ) )
        {
            $handler = array(__CLASS__, 'errorHandler');
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
            $handler = array(__CLASS__, 'exceptionHandler');
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
        $type = \get_class( $e );
        $msg = '[longzox] Uncaught exception: [' . $type . '] ' . $e->getMessage();
        if ( self::$logger instanceof Logger )
        {
            self::$logger->error( $msg . \PHP_EOL . $e->getTraceAsString(), FALSE );
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