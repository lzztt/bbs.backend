<?php

namespace lzx;

use lzx\core\ClassLoader;
use lzx\core\Handler;
use lzx\core\Config;
use lzx\core\Logger;
use lzx\core\Mailer;

/**
 *
 * @property lzx\core\Config $config
 * @property lzx\core\Logger $logger
 */
abstract class App
{

   protected $logger;
   protected $config;
   protected $path;

   public function __construct( $appNamespace, $configFile )
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
         $loader = ClassLoader::getInstance();
         $loader->registerNamespace( __NAMESPACE__, __DIR__ );

         // set ErrorHandler, convert error to ErrorException
         Handler::setErrorHandler();

         // load configuration
         $this->config = Config::getInstance( $configFile );
         $this->path = $this->config->path;

         // register site namespace
         $loader->registerNamespace( $appNamespace, $this->path['server'] );

         // create logger
         $this->logger = Logger::getInstance( $this->path['log'], array( ), TRUE );

         // set logger before set the Exception Handler
         Handler::$logger = $this->logger;
         // set ExceptionHandler
         Handler::setExceptionHandler();

         $webmaster = $this->config->webmaster;
         if ( \filter_var( $webmaster, \FILTER_VALIDATE_EMAIL ) )
         {
            $mailer = new Mailer( $this->config->domain, 'logger' );
            $mailer->to = $webmaster;
            $this->logger->setMailer( $mailer );
         }
      }
      catch ( \Exception $e )
      {
         $msg = '[longzox] App initialization error: ' . $e->getMessage();
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
}

//_END_OF_FILE
