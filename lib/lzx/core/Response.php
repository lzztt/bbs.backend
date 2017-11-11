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
    private $status;
    private $data;
    private $sent;

    private function __construct()
    {
        $this->type = self::HTML;
        $this->status = 200;
        $this->sent = false;
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
        return $this->status;
    }

    public function setContent($data)
    {
        $this->data = $data;
    }

    public function cacheContent(PageCache $cache)
    {
        if ($this->status < 300 && $this->data instanceof Template) {
            $cache->store($this->data);
        } else {
            throw new \Exception('Cache content failed: status=' . $this->status . ' response content type=' . gettype($this->data));
        }
    }

    public function pageNotFound()
    {
        $this->data = null;
        $this->status = 404;
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    }

    public function pageForbidden()
    {
        $this->data = null;
        $this->status = 403;
        header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
    }

    public function pageRedirect($uri)
    {
        $this->data = null;
        $this->status = 302;
        header('Location: ' . $uri);
    }

    public function send()
    {
        if (!$this->sent) {
            // set output header
            switch ($this->type) {
                case self::JSON:
                    header('Content-Type: application/json');
                    break;
                case self::JPEG:
                    header('Content-type: image/jpeg');
                    break;
                default:
                    header('Content-Type: text/html; charset=UTF-8');
            }

            // send page content
            if ($this->data) {
                echo $this->data;
            }

            fastcgi_finish_request();

            $this->sent = true;
        }
    }
}
