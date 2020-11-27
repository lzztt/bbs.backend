<?php

declare(strict_types=1);

namespace lzx\core;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;

class Request
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const URL_INVALID_CHAR = '%'; // encoded uri, sql injection

    public string $domain;
    public string $ip;
    public string $method;
    public string $uri;
    public string $referer;
    public array $data;
    public int $timestamp;
    public string $agent;

    private bool $hasBadData = false;
    private bool $isRobot = false;

    public function __construct(ServerRequest $req)
    {
        $p = $req->getServerParams();
        $this->domain = $p['SERVER_NAME'];
        $this->ip = $p['REMOTE_ADDR'];
        $this->method = $req->getMethod();
        $this->uri = strtolower($p['REQUEST_URI']);
        $this->timestamp = (int) $p['REQUEST_TIME'];
        $this->agent = (string) $p['HTTP_USER_AGENT'];

        $this->isRobot = substr($p['SERVER_PROTOCOL'], 0, 6) === 'HTTP/1'
            || empty($p['HTTPS'])
            || (bool) preg_match('/(http|yahoo|bot|spider)/i', $p['HTTP_USER_AGENT']);

        $inputData = (string) $req->getBody();
        if (!self::validateUrl($this->uri) || ($this->isRobot && strlen($inputData) > 0)) {
            $this->hasBadData = true;
            $this->data = [];
            $this->isRobot = true;
            $this->referer = (string) $p['HTTP_REFERER'];
            return;
        }

        $this->data = self::escapeArray($req->getQueryParams());

        if (in_array($this->method, [self::METHOD_POST, self::METHOD_PUT, self::METHOD_PATCH])) {
            $data = [];
            $contentType = strtolower(explode(';', (string) array_pop($req->getHeader('content-type')))[0]);
            switch ($contentType) {
                case 'application/x-www-form-urlencoded':
                case 'multipart/form-data':
                    if ($this->method === self::METHOD_POST) {
                        $data = $req->getParsedBody();
                    } else {
                        $data = [];
                        parse_str($inputData, $data);
                    }
                    break;
                case 'application/json':
                    $data = json_decode($inputData, true);
            }
            if (!is_array($data)) {
                $data = [];
            }
            $this->data = array_merge($this->data, self::escapeArray($data));
        }

        $arr = explode($this->domain, $p['HTTP_REFERER']);
        $this->referer = sizeof($arr) > 1 ? $arr[1] : '';
    }

    public function isBad(): bool
    {
        return $this->hasBadData;
    }

    public function isRobot(): bool
    {
        return $this->uid === 0 && ($this->hasBadData || $this->isRobot);
    }

    public function isGoogleBot(): bool
    {
        if (str_contains($this->agent, 'Google') === false) {
            return false;
        }

        $host = gethostbyaddr($this->ip);
        if (!$host) {
            return false;
        }

        $domain = implode('.', array_slice(explode('.', $host), -2));
        if (!in_array($domain, ['googlebot.com', 'google.com'])) {
            return false;
        }

        $ip = gethostbyname($host);
        if (!$ip || $ip !== $this->ip) {
            return false;
        }

        return true;
    }

    public function isBingBot(): bool
    {
        if (str_contains($this->agent, 'bing') === false) {
            return false;
        }

        $host = gethostbyaddr($this->ip);
        if (!$host) {
            return false;
        }

        $domain = implode('.', array_slice(explode('.', $host), -3));
        if ($domain !== 'search.msn.com') {
            return false;
        }

        $ip = gethostbyname($host);
        if (!$ip || $ip !== $this->ip) {
            return false;
        }

        return true;
    }

    private static function validateUrl(string $url): bool
    {
        return strpos($url, self::URL_INVALID_CHAR) === false &&
            substr_count($url, '?') < 2;
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
                : (is_array($val)
                    ? self::escapeArray($val)
                    : $val);
            $out[$key] = $val;
        }
        return $out;
    }

    private static function escapeString(string $in): string
    {
        return trim(preg_replace('/<[^>]*>/', '', $in));
    }
}
