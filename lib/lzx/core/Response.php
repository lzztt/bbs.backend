<?php declare(strict_types=1);

namespace lzx\core;

use Exception;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use lzx\core\JpegResponse;
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
    private $resp;

    private function __construct()
    {
        $this->type = self::HTML;
        $this->status = 200;
        $this->sent = false;
        $this->resp = null;
    }

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
            throw new Exception('Cache content failed: status=' . $this->status . ' response content type=' . gettype($this->data));
        }
    }

    public function pageNotFound()
    {
        $this->resp = new EmptyResponse(404);
    }

    public function pageForbidden()
    {
        $this->resp = new EmptyResponse(403);
    }

    public function pageRedirect($uri)
    {
        $this->resp = new RedirectResponse($uri);
    }

    public function send()
    {
        if (!$this->resp) {
            switch ($this->type) {
                case self::JSON:
                    $this->resp = (new JsonResponse($this->data))->withEncodingOptions(JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    break;
                case self::JPEG:
                    $this->resp = new JpegResponse((string) $this->data);
                    break;
                default:
                    $this->resp = new HtmlResponse((string) $this->data);
            }
        }

        if (!$this->sent) {
            $emiter = new SapiEmitter();
            $emiter->emit($this->resp);
            fastcgi_finish_request();

            $this->status = $this->resp->getStatusCode();
            $this->sent = true;
        }
    }
}
