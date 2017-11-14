<?php

namespace lzx\html;

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

    /**
     * @var Logger $logger
     * @static Logger $logger
     */
    private static $logger = null;
    //private static $tpl_cache = []; // pool for rendered templates without $var
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

    /**
     *
     * Constructor
     */
    public function __construct($tpl, array $var = [])
    {
        $this->observers = new \SplObjectStorage();

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
        } catch (\Exception $e) {
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

    /**
     *
     * @param type $name
     * @param type $url
     * @param array $attributes
     * @return \lzx\core\HTMLElement
     */
    public static function link($name, $url, array $attributes = [])
    {
        $attributes['href'] = $url;
        return new HTMLElement('a', $name, $attributes);
    }

    // a list of text links

    /**
     *
     * @param array $list
     * @param array $attributes
     * @param boolean $even_odd
     * @return \lzx\core\HTMLElement
     */
    public static function ulist(array $list, array $attributes = [], $even_odd = true)
    {
        if ($even_odd) {
            if (array_key_exists('class', $attributes)) {
                $attributes['class'] .= ' ' . self::EVEN_ODD_CLASS;
            } else {
                $attributes['class'] = self::EVEN_ODD_CLASS;
            }
        }
        return new HTMLElement('ul', self::_li($list), $attributes);
    }

    /**
     *
     * @param array $list
     * @param array $attributes
     * @return \lzx\core\HTMLElement
     */
    public static function olist(array $list, array $attributes = [])
    {
        return new HTMLElement('ol', self::_li($list), $attributes);
    }

    private static function li($list)
    {
        $list = [];
        foreach ($list as $li) {
            if (is_string($li) || $li instanceof HTMLElement) {
                $list[] = new HTMLElement('li', $li);
            } elseif (is_array($li)) {
                if (!array_key_exists('text', $li)) {
                    throw new \Exception('list data is not found (missing "text" value in array)');
                } elseif (!array_key_exists('attributes', $li)) {
                    throw new \Exception('list attributes is not found (missing "attributes" value in array)');
                } else {
                    $list[] = new HTMLElement('li', $li['text'], $li['attributes']);
                }
            }
        }
        return $list;
    }

    public static function dlist(array $list, array $attributes = [])
    {
        $dl = new HTMLElement('dl', null, $attributes);
        foreach ($list as $li) {
            if (!is_array($li) || sizeof($li) < 2) {
                throw new \Exception('$list need to be an array with dt and dd data');
            }
            $dl->addElements([new HTMLElement('dt', $li['dt']), new HTMLElement('dd', (string) $li['dd'])]);
        }
        return $dl;
    }

    /**
     *
     * @param array $data
     * @param array $attributes
     * @param type $even_odd
     * @return \lzx\core\HTMLElement
     * @throws \Exception
     *
     * $data = [
     *     'caption' => string / HTMLElement('*'),
     *     'thead' => $tr,
     *     'tfoot' => $tr,
     *     'tbody' => [$tr),
     * );
     * $tr = [
     *     'attributes' => [],
     *     'cells' => [$td),
     * );
     * $td = string / HTMLElement('*');
     * $td = [
     *     'attributes' => [],
     *     'text' => string
     * );
     */
    public static function table(array $data, array $attributes = [], $even_odd = true)
    {
        $table = new HTMLElement('table', null, $attributes);
        if (array_key_exists('caption', $data) && strlen($data['caption']) > 0) {
            $table->addElement(new HTMLElement('caption', $data['caption']));
        }
        if (array_key_exists('thead', $data) && sizeof($data['thead']) > 0) {
            $table->addElement(new HTMLElement('thead', self::_table_row($data['thead'], true)));
        }
        if (array_key_exists('tfoot', $data) && sizeof($data['tfoot']) > 0) {
            $table->addElement(new HTMLElement('tfoot', self::_table_row($data['tfoot'])));
        }
        if (!array_key_exists('tbody', $data)) {
            throw new \Exception('table body (tbody) data is not found');
        }

        $tbody_attr = $even_odd ? ['class' => self::EVEN_ODD_CLASS] : [];

        $tbody = new HTMLElement('tbody', null, $tbody_attr);

        foreach ($data['tbody'] as $tr) {
            $tbody->addElement(self::_table_row($tr));
        }

        $table->addElement($tbody);

        return $table;
    }

    private static function tableRow($row, $isHeader = false)
    {
        if (!array_key_exists('cells', $row)) {
            throw new \Exception('row cells (tr) data is not found');
        }
        if (array_key_exists('attributes', $row)) {
            $tr = new HTMLElement('tr', null, $row['attributes']);
        } else {
            $tr = new HTMLElement('tr', null);
        }

        $tag = $isHeader ? 'th' : 'td';
        foreach ($row['cells'] as $td) {
            if (is_string($td) || $td instanceof HTMLElement) {
                $tr->addElement(new HTMLElement($tag, $td));
            } elseif (is_array($td)) {
                if (!array_key_exists('text', $td)) {
                    throw new \Exception('cell data is not found (missing "text" value in array)');
                }

                if (array_key_exists('attributes', $td)) {
                    $tr->addElement(new HTMLElement($tag, $td['text'], $td['attributes']));
                } else {
                    $tr->addElement(new HTMLElement($tag, $td['text']));
                }
            }
        }
        return $tr;
    }

    /*
     * $form
     */

    public static function form($inputs, $action, $method = 'get', $attributes = [])
    {
        //input text, radio, checkbox, textarea,
        $attributes['action'] = $action;
        $attributes['method'] = in_array($method, ['get', 'post']) ? $method : 'get';
    }

    /*
     * [
     *     'name' => $name,
     *     'label' => $label,
     *     'class' => $class,
     *     'help' => $help,
     *     'attributes' => [
     *         'class' => $class,
     *         'required' => $required,
     *
     * form:
     * //fieldset - legend
     * //input - label [required, helper] // id = name
     * //textarea - label [required, helper]
     * // select -option -optgroup -label [required, helper]
     * // button [submit, reset]
     *     )
     *
     * textInput
     * checkboxInput
     * radioInput
     * emailInput
     * passwordInput
     * hiddenInput
     * fileInput
     * );
     */

    public static function select($name, $type, $label, array $options, $attributes)
    {
        if ($type == 'checkbox' || $type == 'radio') {
            $list = new HTMLElement('ul', null, ['class' => 'select_options']);
            $i = 0;
            foreach ($options as $op) {
                $i++;
                $option = new HTMLElement('li');
                $input_id = implode('_', [$type, $name, $i]);
                $input_attr = [
                    'id' => $input_id,
                    'type' => $type,
                    'name' => $name,
                    'value' => $op['value']
                ];
                $option->addElement(new HTMLElement('input', null, $input_attr));
                $option->addElement(new HTMLElement('label', $op['text'], ['for' => $input_id]));
                $list->addElement($option);
            }
        }
    }

    public static function input($name, $type, $label = '', $attributes = [])
    {
        if ($type == 'radio' || $type == 'checkbox') {
            return new HTMLElement('ul');
        }
        $label_div = new HTMLElement('div', new HTMLElement('label', $label, ['for' => $name]));
        if (array_key_exists('title', $attributes)) {
            //$label_div->data = []
        }
        $input_div = new HTMLElement('div', new HTMLElement('input', null, $attributes), ['class' => 'input_div']);
        //  <div>
        //          <input id="element_1" name="element_1" class="element text medium" type="text" maxlength="255" value=""/>
        //  </div>
    }

    public static function uri(array $args = [], array $get = [])
    {
        $conditions = [];
        foreach ($get as $k => $v) {
            $conditions[] = $k . '=' . $v;
        }
        $query = implode('&', $conditions);

        return htmlspecialchars('/' . implode('/', $args) . ($query ? '?' . $query : ''));
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

    public static function navbar(array $links, $active_link = null)
    {
        $list = [];
        foreach ($links as $text => $uri) {
            $list[] = self::link($text, $uri, $uri == $active_link ? ['class' => 'active'] : []);
        }

        return new HTMLElement('nav', $list, ['class' => 'navbar']);
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
