<?php declare(strict_types=1);

namespace lzx;

use Exception;
use lzx\core\ClassLoader;
use lzx\core\Handler;
use lzx\core\Logger;

abstract class App
{
    public $domain;
    protected $logger;
    protected $loader;

    public function __construct()
    {
        // start auto loader
        $file = __DIR__ . '/core/ClassLoader.php';
        if (is_file($file) && is_readable($file)) {
            require_once $file;
        } else {
            throw new Exception('cannot load autoloader class');
        }

        // register namespaces
        $this->loader = ClassLoader::getInstance();
        $this->loader->registerNamespace(__NAMESPACE__, __DIR__);

        // set ErrorHandler, all error would be convert to ErrorException from now on
        Handler::setErrorHandler();

        // create logger
        $this->logger = Logger::getInstance();
        // set logger for Handler
        Handler::$logger = $this->logger;
        // set ExceptionHandler
        Handler::setExceptionHandler();
    }

    abstract public function run($argc, array $argv);
}
