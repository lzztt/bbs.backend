<?php declare(strict_types=1);

namespace lzx\core;

use Exception;
use InvalidArgumentException;
use lzx\core\Mailer;

class Logger
{
// static class instead of singleton

    const INFO = 'INFO';
    const DEBUG = 'DEBUG';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';

    private $userinfo = [];
    private $dir;
    private $file;
    private $time;
    private $mailer = null;
    private $logCache;

    private function __construct()
    {
        $this->file = [
            self::INFO => 'php_info.log',
            self::DEBUG => 'php_debug.log',
            self::WARNING => 'php_warning.log',
            self::ERROR => 'php_error.log'
        ];
        // initialize cache
        $this->logCache = [
            self::INFO => '',
            self::DEBUG => '',
            self::WARNING => '',
            self::ERROR => ''
        ];
        $this->time = date('Y-m-d H:i:s T', (int) $_SERVER['REQUEST_TIME']);
    }

    public function __destruct()
    {
        $this->flush();
    }

    public static function getInstance(): Logger
    {
        static $instance;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    // only set Dir once
    public function setDir(string $dir): void
    {
        if (!isset($this->dir)) {
            if (is_dir($dir) && is_writable($dir)) {
                foreach ($this->file as $l => $f) {
                    $this->file[$l] = $dir . '/' . $f;
                }
                $this->dir = $dir;
            } else {
                throw new InvalidArgumentException('Log dir is not an readable directory : ' . $dir);
            }
        } else {
            throw new Exception('Logger dir has already been set, and could only be set once');
        }
    }

    public function setEmail(string $email): void
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->mailer = new Mailer('logger');
            $this->mailer->to = $email;
        } else {
            throw new Exception('Invalid email address: ' . $email);
        }
    }

    public function setUserInfo(array $userinfo): void
    {
        $this->userinfo = $userinfo;
    }

    public function info(string $str): void
    {
        $this->log($str, self::INFO);
    }

    public function debug($var): void
    {
        ob_start();
        var_dump($var);
        $str = ob_get_contents();    // Get the contents of the buffer
        ob_end_clean();

        $this->log(trim($str), self::DEBUG);
    }

    public function warn(string $str): void
    {
        $this->log($str, self::WARNING);
    }

    public function error(string $str, array $traces = []): void
    {
        $this->log($str, self::ERROR, $traces);
    }

    public function flush(): void
    {
        if ($this->dir) {
            foreach ($this->logCache as $type => $log) {
                if ($log) {
                    file_put_contents($this->file[$type], $log, FILE_APPEND | LOCK_EX);
                }
            }
        } else {
            error_log(implode('', $this->logCache));
        }

        // clear the cache
        $this->logCache = [
            self::INFO => '',
            self::DEBUG => '',
            self::WARNING => '',
            self::ERROR => ''
        ];
    }

    private function log(string $str, string $type, array $traces = null): void
    {
        $log = ['time' => $this->time, 'type' => $type];
        foreach ($this->userinfo as $k => $v) {
            $log[$k] = $v;
        }
        $log['uri'] = $_SERVER['REQUEST_URI'];
        $log['client'] = $_SERVER['REMOTE_ADDR'] . ' : ' . $_SERVER['HTTP_USER_AGENT'];
        $log['message'] = $str;

        if ($traces) {
            $log['trace'] = $this->getBacktrace($traces);
        }

        $msg = str_replace('\n', "\n", json_encode($log, JSON_NUMERIC_CHECK
                | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        if ($type == self::ERROR && isset($this->mailer)) {
            $this->mailer->subject = 'web error: ' . $_SERVER['REQUEST_URI'];
            $this->mailer->body = $msg;
            $this->mailer->send();
            $log['_SERVER'] = $_SERVER;
        }

        $this->logCache[$type] .=  $msg . PHP_EOL;
    }

    private function getBacktrace(array $traces): array
    {
        if (empty($traces)) {
            $traces = array_slice(debug_backtrace(), 2);
        }
        $ret = [];

        foreach ($traces as $i => $call) {
            $ret[] = '#' . str_pad((string) $i, 3, ' ')
                . ($call['class'] ? $call['class'] . $call['type'] . $call['function'] : $call['function'])
                . ' @ ' . $call['file'] . ':' . $call['line'];
        }

        return $ret;
    }
}
