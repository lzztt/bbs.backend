<?php

namespace lzx;

use lzx\core\ClassLoader;
use lzx\core\Handler;
use lzx\core\Logger;
use lzx\core\Config;
use lzx\core\Mailer;

/**
 *
 * @property lzx\core\Config $config
 * @property lzx\core\Logger $logger
 */
abstract class App
{

   protected $path;
   protected $logger;
   protected $config;

   public function __construct($appNamespace, $appDir)
   {
      try
      {
         if (\mb_internal_encoding("UTF-8") === FALSE)
         {
            throw new \Exception('failed to set utf-8 encoding');
         }

         if (\is_dir($appDir) && \is_readable($appDir))
         {
            $this->path = array(
               'root' => $appDir,
               'log' => $appDir . '/logs',
            );
         }
         else
         {
            throw new \Exception('appDir is not accessible');
         }

         $_file = __DIR__ . '/core/ClassLoader.php';
         if (\is_file($_file) && \is_readable($_file))
         {
            require_once $_file;
         }
         else
         {
            throw new \Exception('cannot load autoloader class');
         }
         $loader = ClassLoader::getInstance();
         $loader->registerNamespace(__NAMESPACE__, __DIR__);
         $loader->registerNamespace($appNamespace, $appDir);

         // set ErrorHandler, convert error to ErrorException
         Handler::setErrorHandler();

         $this->logger = Logger::getInstance($this->path['log'], array(), TRUE);

         // set logger before set the Exception Handler
         Handler::$logger = $this->logger;
         // set ExceptionHandler
         Handler::setExceptionHandler();

         // load site config and class config
         $this->config = Config::getInstance($appDir . '/config.php');
         $webmaster = $this->config->webmaster;
         if (\filter_var($webmaster, \FILTER_VALIDATE_EMAIL))
         {
            $mailer = new Mailer($this->config->domain, 'logger');
            $mailer->to = $webmaster;
            $this->logger->setMailer($mailer);
         }
      }
      catch (\Exception $e)
      {
         exit('[longzox] App initialization error: ' . $e->getMessage());
      }
   }

   abstract public function run($argc, $argv);
}

//_END_OF_FILE
