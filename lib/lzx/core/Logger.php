<?php declare(strict_types=1);

namespace lzx\core;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogger;
use Throwable;
use lzx\core\TraceProcessor;

class Logger extends MonoLogger
{
    private $mailHandler;

    public static function getInstance(): Logger
    {
        static $instance;

        if (!$instance) {
            $instance = new static();
        }

        return $instance;
    }

    private function __construct()
    {
        parent::__construct('hbbs');
    }

    public function setFile(string $file): void
    {
        $this->pushHandler(new BufferHandler(new StreamHandler($file)));
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

    public function addExtraInfo(array $extra): void
    {
        $this->mailHandler->pushProcessor(function (array $record) use ($extra): array {
            $record['extra'] += $extra;
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
