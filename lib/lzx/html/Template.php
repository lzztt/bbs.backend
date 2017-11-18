<?php declare(strict_types=1);

namespace lzx\html;

use Exception;
use SplObjectStorage;
use lzx\core\Logger;
use lzx\html\HTMLElement;
use lzx\core\Controller;

class Template
{
    const EVEN_ODD_CLASS = 'even_odd_parent';

    public static $path;
    public static $theme;
    public static $language;
    public static $debug = false;
    private static $hasError = false;
    private static $site;

    private static $logger = null;
    public $tpl;
    private $var = [];
    private $observers;
    private $string;

    /**
     * Observer design pattern interfaces
     */
    public function attach(Controller $observer)
    {
        $this->observers->attach($observer);
    }

    public function detach(Controller $observer)
    {
        $this->observers->detach($observer);
    }

    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    public function __construct($tpl, array $var = [])
    {
        $this->observers = new SplObjectStorage();

        $this->tpl = $tpl;
        if ($var) {
            $this->var = $var;
        }
    }

    public function setVar(array $var)
    {
        $this->var = array_merge($this->var, $var);
    }

    public static function setSite($site)
    {
        self::$site = $site;
    }

    public function __toString()
    {
        // return from string cache
        if ($this->string) {
            return $this->string;
        }

        // build the template
        try {
            // notify observers
            $this->notify();

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
                $output = 'template loading error: [' . $tpl_theme . ':' . $tpl . ']';
            } else {
                ob_start();                            // Start output buffering
                include $tpl_file;                    // Include the template file
                $output = ob_get_contents();     // Get the contents of the buffer
                ob_end_clean();                      // End buffering and discard
            }
        } catch (Exception $e) {
            ob_end_clean();
            self::$hasError = true;
            if (isset(self::$logger)) {
                self::$logger->error($e->getMessage(), $e->getTrace());
            }
            $output = 'template parsing error: [' . $tpl_theme . ':' . $tpl . ']';
        }

        // save to cache
        $this->string = $output;
        return $output;
    }

    public static function setLogger(Logger $logger)
    {
        self::$logger = $logger;
    }

    public static function hasError()
    {
        return self::$hasError;
    }

    public static function formatTime($timestamp)
    {
        return date('m/d/Y H:i', $timestamp);
    }

    public static function truncate($str, $len = 45)
    {
        if (strlen($str) < $len / 2) {
            return $str;
        }
        $mb_len = mb_strlen($str);
        $rate = sqrt($mb_len / strlen($str)); // sqrt(0.7) = 0.837
        $s_len = ($rate > 0.837 ? ceil($len * $rate) : floor(($len - 2) * $rate));
        // the cut_off length is depend on the rate of non-single characters
        //var_dump(implode(' - ', [strlen($str), $mb_len, $s_len, $rate, $str,  mb_substr($str, 0, $s_len))));
        return ($mb_len > $s_len) ? mb_substr($str, 0, $s_len) : $str;
    }

// local time function. do not touch them
// the following two functions convert between standard TIMESTAMP and local time
// we only store timestamp in database, for query and comparation
// we only display local time based on timezones
// do not use T in format, timezone info is not correct
    public static function localDate($format, $timestamp)
    {
        return date($format, TIMESTAMP + ($_COOKIE['timezone'] - SYSTIMEZONE) * 3600);
    }

    // get chinese date and time
    public static function getWeekday($timestamp)
    {
        static $weekdays = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];

        return $weekdays[date('w', $timestamp)];
    }

    public static function getDateTime($timestamp)
    {
        return date('Y年m月d日 H:i', $timestamp);
    }

// do not use timezone info in the $time string
    public static function localStrToTime($time)
    {
        return (strtotime($time) - ($_COOKIE['timezone'] - SYSTIMEZONE) * 3600);
    }

    public static function link($name, $url, array $attributes = [])
    {
        $attributes['href'] = $url;
        return new HTMLElement('a', $name, $attributes);
    }

    public static function breadcrumb(array $links)
    {
        $list = [];
        $count = count($links) - 1;
        foreach ($links as $text => $uri) {
            $list[] = $count-- ? self::link($text, $uri) : (string) $text;
        }

        return new HTMLElement('nav', $list, ['class' => 'breadcrumb']);
    }

    public static function pager($pageNo, $pageCount, $uri)
    {
        if ($pageCount < 2) {
            return null;
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
        return new HTMLElement('nav', $pager, ['class' => 'pager']);
    }
}
