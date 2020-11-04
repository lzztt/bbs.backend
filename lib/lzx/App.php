<?php

declare(strict_types=1);

namespace lzx;

use ErrorException;
use Monolog\ErrorHandler;
use lzx\core\Logger;

abstract class App
{
    public $domain;
    protected $logger;

    public function __construct()
    {
        self::errorToException();
        $this->logger = Logger::getInstance();
        ErrorHandler::register($this->logger, false);
    }

    abstract public function run(array $args): void;

    private static function errorToException(): void
    {
        set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }, error_reporting());
    }
}
