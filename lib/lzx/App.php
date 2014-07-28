<?php

namespace lzx;

use lzx\core\ClassLoader;
use lzx\core\Handler;
use lzx\core\Logger;
use lzx\core\Mailer;

/**
 *
 * @property lzx\core\Config $config
 * @property lzx\core\Logger $logger
 */
abstract class App
{

   public $domain;
   protected $logger;
   protected $loader;

   public function __construct()
   {
      try
      {
         if ( \mb_internal_encoding( "UTF-8" ) === FALSE )
         {
            throw new \Exception( 'failed to set utf-8 encoding' );
         }

         // start auto loader
         $_file = __DIR__ . '/core/ClassLoader.php';
         if ( \is_file( $_file ) && \is_readable( $_file ) )
         {
            require_once $_file;
         }
         else
         {
            throw new \Exception( 'cannot load autoloader class' );
         }

         // register namespaces
         $this->loader = ClassLoader::getInstance();
         $this->loader->registerNamespace( __NAMESPACE__, __DIR__ );

         // set ErrorHandler, all error would be convert to ErrorException from now on
         Handler::setErrorHandler();

         // create logger
         $this->logger = Logger::getInstance();
         // set logger for Handler
         Handler::$logger = $this->logger;
         // set ExceptionHandler
         Handler::setExceptionHandler();
      }
      catch ( \Exception $e )
      {
         $msg = 'App initialization error: ' . $e->getMessage();
         if ( $this->logger instanceof Logger )
         {
            $this->logger->error( $msg . \PHP_EOL . $e->getTraceAsString(), FALSE );
         }
         else
         {
            \error_log( $msg . \PHP_EOL . $e->getTraceAsString() );
         }
         exit( $msg );
      }
   }

   abstract public function run( $argc, Array $argv );

   public function setLogDir( $dir )
   {
      $this->logger->setLogDir( $dir );
   }

   public function setLogMailer( $email )
   {
      if ( $email )
      {
         if ( \filter_var( $email, \FILTER_VALIDATE_EMAIL ) )
         {
            $mailer = new Mailer( 'logger' );
            $mailer->to = $email;
            $this->logger->setMailer( $mailer );
         }
         else
         {
            throw new \Exception( 'Invalid email address: ' . $email );
         }
      }
   }

}

//_END_OF_FILE
