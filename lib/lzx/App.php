<?php

namespace lzx;

use lzx\core\ClassLoader;
use lzx\core\Handler;
use lzx\core\Logger;

/**
 *
 * @property Logger $logger
 */
abstract class App
{

   public $domain;
   protected $logger;
   protected $loader;

   public function __construct()
   {      
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

   abstract public function run( $argc, Array $argv );

}

//_END_OF_FILE
