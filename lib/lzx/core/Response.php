<?php

namespace lzx\core;

use lzx\html\Template;
use lzx\cache\PageCache;

class Response
{
    const HTML = 'html';
    const JSON = 'json';
    const JPEG = 'jpeg';

    public $type;
    private $_status;
    private $_data;
    private $_sent;

    private function __construct()
    {
        $this->type = self::HTML;
        $this->_status = 200;
        $this->_sent = false;
    }

    /**
     *
     * @staticvar self $instance
     * @return \lzx\core\Response
     */
    public static function getInstance()
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new self();
        }
        return $instance;
    }

    public function getStatus()
    {
        return $this->_status;
    }

    public function setContent($data)
    {
        $this->_data = $data;
    }

    public function cacheContent(PageCache $cache)
    {
        if ($this->_status < 300 && $this->_data instanceof Template) {
            $cache->store($this->_data);
        } else {
            throw new \Exception('Cache content failed: status=' . $this->_status . ' response content type=' . \gettype($this->_data));
        }
    }

    public function pageNotFound()
    {
        $this->_data = null;
        $this->_status = 404;
        \header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    }

    public function pageForbidden()
    {
        $this->_data = null;
        $this->_status = 403;
        \header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
    }

    public function pageRedirect($uri)
    {
        $this->_data = null;
        $this->_status = 302;
        \header('Location: ' . $uri);
    }

    public function send()
    {
        if (!$this->_sent) {
            // set output header
            switch ($this->type) {
                case self::JSON:
                    \header('Content-Type: application/json');
                    break;
                case self::JPEG:
                    \header('Content-type: image/jpeg');
                    break;
                default:
                    \header('Content-Type: text/html; charset=UTF-8');
            }

            // send page content
            if ($this->_data) {
                echo $this->_data;
            }

            \fastcgi_finish_request();

            $this->_sent = true;
        }
    }
}

//__END_OF_FILE__
