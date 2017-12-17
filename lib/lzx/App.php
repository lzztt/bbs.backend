<?php declare(strict_types=1);

namespace lzx;

use lzx\core\Handler;
use lzx\core\Logger;

abstract class App
{
    public $domain;
    protected $logger;

    public function __construct()
    {
        // set ErrorHandler, all error would be convert to ErrorException from now on
        Handler::setErrorHandler();

        // create logger
        $this->logger = Logger::getInstance();
        // set logger for Handler
        Handler::$logger = $this->logger;
        // set ExceptionHandler
        Handler::setExceptionHandler();
    }

    abstract public function run($argc, array $argv): void;
}
