<?php declare(strict_types = 1);

namespace lzx\core;

use Throwable;

class TraceProcessor
{
    private $pathPrefixToTrim;
    private $pathPrefixLength;

    public function __construct(string $pathPrefixToTrim = '')
    {
        $this->pathPrefixToTrim = $pathPrefixToTrim;
        $this->pathPrefixLength = strlen($pathPrefixToTrim);
    }

    public function __invoke(array $record)
    {
        if (array_key_exists('exception', $record['context']) && $record['context']['exception'] instanceof Throwable) {
            $traces = array_reverse($record['context']['exception']->getTrace());
            unset($record['context']['exception']);
        } else {
            $traces = self::getCurrentTrace();
        }
        $record['extra']['trace'] = $this->formatTrace($traces);
        return $record;
    }

    private static function getCurrentTrace(): array
    {
        $traces = array_reverse(debug_backtrace(~DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS));
        $cutoff = false;
        return array_filter($traces, function ($v, $k) use (&$cutoff): bool {
            if ($cutoff !== false && $k > $cutoff) {
                return false;
            }
            if (strpos($v['class'], 'Monolog\\') !== false) {
                $cutoff = $k;
                return false;
            }
            return true;
        }, ARRAY_FILTER_USE_BOTH);
    }

    private function formatTrace(array $traces): array
    {
        return array_map(function (array $frame): string {
            return (array_key_exists('class', $frame) ? $frame['class'] . $frame['type'] . $frame['function'] : $frame['function'])
            . ' @' . (array_key_exists('file', $frame) ? $this->trimPrefix($frame['file']) : '') . ':' . $frame['line'];
        }, $traces);
    }

    private function trimPrefix(string $path): string
    {
        if ($this->pathPrefixLength > 0 && substr($path, 0, $this->pathPrefixLength) === $this->pathPrefixToTrim) {
            return substr($path, $this->pathPrefixLength);
        }
        return $path;
    }
}
