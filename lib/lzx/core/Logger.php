<?php

declare(strict_types=1);

namespace lzx\core;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogger;
use Monolog\LogRecord;
use Throwable;
use lzx\core\TraceProcessor;

class Logger extends MonoLogger
{
    private $mailHandler;

    public static function getInstance(): Logger
    {
        static $instance;

        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }

    private function __construct()
    {
        parent::__construct('hbbs');
    }

    public function setFile(string $file): void
    {
        $handler = new BufferHandler(new StreamHandler($file));
        // $handler->pushProcessor(new TraceProcessor(self::getPathPrefix())); // debug @ dev
        $this->pushHandler($handler);
    }

    public function setEmail(string $to, string $subject, string $from): void
    {
        $mailHandler = new NativeMailerHandler($to, $subject, $from);
        $mailHandler->setContentType('text/html');
        $mailHandler->setFormatter(new HtmlFormatter());
        $this->mailHandler = new BufferHandler($mailHandler);
        $this->mailHandler->pushProcessor(new TraceProcessor(self::getPathPrefix()));
        $this->pushHandler($this->mailHandler);
    }

    private static function getPathPrefix(): string
    {
        $path = explode(DIRECTORY_SEPARATOR, __FILE__);
        $endCount = -3 - substr_count(__NAMESPACE__, '\\');
        return implode(DIRECTORY_SEPARATOR, array_slice($path, 0, $endCount)) . DIRECTORY_SEPARATOR;
    }

    public function addContext(array $context): void
    {
        $this->mailHandler->pushProcessor(function (LogRecord $record) use ($context): LogRecord {
            $record->extra += $context;
            return $record;
        });
    }

    public function logException(Throwable $e): void
    {
        $this->error(get_class($e) . ': "' . $e->getMessage() . '" @ ' . $e->getFile() . ':' . $e->getLine(), ['exception' => $e]);
    }

    public function flush(): void
    {
        array_walk($this->handlers, function ($handler) {
            $handler->flush();
        });
    }
}
