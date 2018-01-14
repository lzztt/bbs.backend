<?php declare(strict_types = 1);

namespace lzx\core;

use Throwable;

class TraceProcessor
{

    public function __invoke(array $record)
    {
        if (array_key_exists('exception', $record['context']) && $record['context']['exception'] instanceof Throwable) {
            $traces = array_reverse($record['context']['exception']->getTrace());
            unset($record['context']['exception']);
        } else {
            $traces = self::getCurrentTrace();
        }
        $record['extra']['trace'] = self::formatTrace($traces);
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

    private static function formatTrace(array $traces): array
    {
        $i = 0;
        return array_map(function (array $call) use (&$i): string {
            return $i++ . ' '
                    . ($call['class'] ? $call['class'] . $call['type'] . $call['function'] : $call['function'])
                    . ' @ ' . $call['file'] . ':' . $call['line'];
        }, $traces);
    }
}
