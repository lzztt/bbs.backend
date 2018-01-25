<?php declare(strict_types=1);

namespace lzx\html;

use Exception;
use lzx\core\Logger;
use lzx\html\HTMLElement;

class Template
{
    public static $path;
    public static $theme;
    public static $debug = false;
    private static $hasError = false;
    private static $site;

    private static $logger = null;
    public $tpl;
    private $var = [];
    private $onBeforeRender = [];
    private $cache;

    public function __construct(string $tpl, array $var = [])
    {
        $this->tpl = $tpl;
        if ($var) {
            $this->var = $var;
        }
    }

    public function onBeforeRender(callable $callback): void
    {
        $this->onBeforeRender[] = $callback;
    }

    public function setVar(array $var): void
    {
        $this->var = array_merge($this->var, $var);
    }

    public static function setSite(string $site): void
    {
        self::$site = $site;
    }

    public function __toString()
    {
        if ($this->cache) {
            return $this->cache;
        }

        foreach ($this->onBeforeRender as $callback) {
            $callback($this);
        }

        extract($this->var);
        $tpl = $this->tpl;
        $tpl_theme = self::$theme;
        $tpl_path = self::$path . '/' . self::$theme;
        $tpl_debug = self::$debug;

        // check site files first
        if (self::$site) {
            $tpl_file = $tpl_path . '/' . $tpl . '.' . self::$site . '.tpl.php';
            if (!is_file($tpl_file) || !is_readable($tpl_file)) {
                $tpl_file = $tpl_path . '/' . $tpl . '.tpl.php';
            }
        } else {
            $tpl_file = $tpl_path . '/' . $tpl . '.tpl.php';
        }

        if (!is_file($tpl_file) || !is_readable($tpl_file)) {
            self::$hasError = true;
            $output = 'template loading error: ' . implode(DIRECTORY_SEPARATOR, [$tpl_theme, $tpl]);
        } else {
            try {
                ob_start();
                include $tpl_file;
                $output = ob_get_contents();
                ob_end_clean();
            } catch (Exception $e) {
                ob_end_clean();
                self::$hasError = true;
                $output = 'template parsing error: ' . implode(DIRECTORY_SEPARATOR, [$tpl_theme, $tpl]);
                if (isset(self::$logger)) {
                    self::$logger->logException($e);
                }
            }
        }

        $this->cache = $output;
        return $output;
    }

    public static function setLogger(Logger $logger): void
    {
        self::$logger = $logger;
    }

    public static function hasError(): bool
    {
        return self::$hasError;
    }

    public static function link(string $name, string $url, array $attributes = []): HTMLElement
    {
        $attributes['href'] = $url;
        return new HTMLElement('a', $name, $attributes);
    }

    public static function breadcrumb(array $links): HTMLElement
    {
        $list = [];
        $count = count($links) - 1;
        foreach ($links as $text => $uri) {
            $list[] = $count-- ? self::link($text, $uri) : (string) $text;
        }

        return new HTMLElement('nav', $list, ['class' => 'breadcrumb']);
    }

    public static function pager(int $pageNo, int $pageCount, string $uri): string
    {
        if ($pageCount < 2) {
            return '';
        }

        if ($pageCount <= 7) {
            $pageFirst = 1;
            $pageLast = $pageCount;
        } else {
            $pageFirst = $pageNo - 3;
            $pageLast = $pageNo + 3;
            if ($pageFirst < 1) {
                $pageFirst = 1;
                $pageLast = 7;
            } elseif ($pageLast > $pageCount) {
                $pageFirst = $pageCount - 6;
                $pageLast = $pageCount;
            }
        }

        if ($pageNo != 1) {
            $pager[] = self::link('<<', $uri);
            $pager[] = self::link('<', $uri . '?p=' . ($pageNo - 1));
        }
        for ($i = $pageFirst; $i <= $pageLast; $i++) {
            if ($i == $pageNo) {
                $pager[] = self::link((string) $i, $uri . '?p=' . $i, ['class' => 'active']);
            } else {
                $pager[] = self::link((string) $i, $uri . '?p=' . $i);
            }
        }
        if ($pageNo != $pageCount) {
            $pager[] = self::link('>', $uri . '?p=' . ($pageNo + 1));
            $pager[] = self::link('>>', $uri . '?p=' . $pageCount);
        }
        return (string) new HTMLElement('nav', $pager, ['class' => 'pager']);
    }
}
