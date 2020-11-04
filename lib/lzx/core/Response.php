<?php

declare(strict_types=1);

namespace lzx\core;

use Exception;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use lzx\cache\PageCache;
use lzx\core\JpegResponse;
use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use lzx\exception\NotFound;
use lzx\exception\Redirect;
use lzx\html\Template;

class Response
{
    const HTML = 0;
    const JSON = 1;
    const JPEG = 2;

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

    public static function getInstance(): Response
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new self();
        }
        return $instance;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setContent($data): void
    {
        $this->data = $data;
    }

    public function cacheContent(PageCache $cache): void
    {
        if ($this->status < 300 && $this->data instanceof Template) {
            $cache->setData($this->data);
        } else {
            throw new Exception('Cache content failed: status=' . $this->status . ' response content type=' . gettype($this->data));
        }
    }

    public function handleException(Exception $e): void
    {
        if ($e instanceof ErrorMessage) {
            if ($this->type === Response::JSON) {
                $this->setContent(['error' => $e->getMessage()]);
            } else {
                $this->setContent($e->getMessage());
            }
        } elseif ($e instanceof Forbidden) {
            if ($this->type === Response::JSON) {
                $this->setContent(['error' => 'Forbidden']);
            } else {
                $this->resp = new EmptyResponse(403);
            }
        } elseif ($e instanceof NotFound) {
            if ($this->type === Response::JSON) {
                $this->setContent(['error' => 'Not Found']);
            } else {
                $this->resp = new EmptyResponse(404);
            }
        } elseif ($e instanceof Redirect) {
            if ($this->type === Response::JSON) {
                $this->setContent(['redirect' => $e->getMessage()]);
            } else {
                $this->resp = new RedirectResponse($e->getMessage());
            }
        } else {
            throw $e;
        }
    }

    public function send(): void
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
