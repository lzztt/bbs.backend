<?php declare(strict_types=1);

namespace lzx\core;

use Laminas\Diactoros\ServerRequestFactory;

class Request
{
    const METHOD_GET = 'get';
    const METHOD_POST = 'post';
    const QUERY_INVALID_CHAR = '%';

    public $domain;
    public $ip;
    public $method;
    public $uri;
    public $referer;
    public $data;
    public $uid;
    public $timestamp;

    private $req;
    private $hasBadUrl;
    private $isRobot;

    private function __construct()
    {
        $this->req = ServerRequestFactory::fromGlobals();

        $params = $this->req->getServerParams();
        $this->domain = $params['SERVER_NAME'];
        $this->ip = $params['REMOTE_ADDR'];
        $this->method = strtolower($this->req->getMethod());
        $this->uri = strtolower($params['REQUEST_URI']);
        $this->timestamp = (int) $params['REQUEST_TIME'];

        $this->hasBadUrl = false;
        if (strpos($this->uri, self::QUERY_INVALID_CHAR) !== false) {
            $this->hasBadUrl = true;
            $this->data = [];
            $this->isRobot = true;
            return;
        }

        $this->data = self::escapeArray($this->req->getQueryParams());

        if ($this->method === self::METHOD_POST) {
            $contentType = strtolower(explode(';', (string) $this->req->getHeader('content-type')[0])[0]);
            switch ($contentType) {
                case 'application/x-www-form-urlencoded':
                case 'multipart/form-data':
                    $this->data = array_merge($this->data, self::escapeArray($this->req->getParsedBody()));
                    break;
                case 'application/json':
                    $this->data = array_merge($this->data, json_decode((string) $this->req->getBody(), true));
            }
        }

        $arr = explode($this->domain, $params['HTTP_REFERER']);
        $this->referer = sizeof($arr) > 1 ? $arr[1] : null;
        $this->isRobot = (bool) preg_match('/(http|yahoo|bot|spider)/i', $params['HTTP_USER_AGENT']);
    }

    public static function getInstance(): Request
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new self();
        }
        return $instance;
    }

    public function isBad(): bool
    {
        return $this->hasBadUrl;
    }

    public function isRobot(): bool
    {
        return $this->hasBadUrl || $this->isRobot;
    }

    private static function escapeArray(array $in): array
    {
        $out = [];
        foreach ($in as $key => $val) {
            $key = is_string($key)
                    ? self::escapeString($key)
                    : $key;
            $val = is_string($val)
                    ? self::escapeString($val)
                    : self::escapeArray($val);
            $out[$key] = $val;
        }
        return $out;
    }

    private static function escapeString(string $in): string
    {
        return trim(preg_replace('/<[^>]*>/', '', $in));
    }
}
