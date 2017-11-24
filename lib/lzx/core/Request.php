<?php declare(strict_types=1);

namespace lzx\core;

use Zend\Diactoros\ServerRequestFactory;

class Request
{
    public $domain;
    public $ip;
    public $uri;
    public $referer;
    public $post;
    public $get;
    public $files;
    public $json;
    public $uid;
    public $timestamp;
    private $req;

    private function __construct()
    {
        $this->req = ServerRequestFactory::fromGlobals();

        $params = $this->req->getServerParams();
        $this->domain = $params['HTTP_HOST'];
        $this->ip = $params['REMOTE_ADDR'];
        $this->uri = strtolower($params['REQUEST_URI']);

        $this->timestamp = (int) $params['REQUEST_TIME'];

        $this->post = self::escape($this->req->getParsedBody());
        $this->get = self::escape($this->req->getQueryParams());
        $this->files = $this->getUploadFiles();
        $this->json = json_decode($this->req->getBody()->getContents(), true);

        $arr = explode($this->domain, $params['HTTP_REFERER']);
        $this->referer = sizeof($arr) > 1 ? $arr[1] : null;
    }

    public static function getInstance()
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new self();
        }
        return $instance;
    }

    public function getUploadFiles()
    {
        static $files;

        if (!isset($files)) {
            $files = [];
            foreach ($_FILES as $type => $file) {
                $files[$type] = [];
                if (is_array($file['error'])) { // file list
                    for ($i = 0; $i < sizeof($file['error']); $i++) {
                        foreach (array_keys($file) as $key) {
                            $files[$type][$i][$key] = $file[$key][$i];
                        }
                    }
                } else // single file
                {
                    $files[$type][] = $file;
                }
            }
        }

        return $files;
    }

    private static function escape($in)
    {
        if (is_array($in)) {
            $out = [];
            foreach ($in as $key => $value) {
                $out[self::escape($key)] = self::escape($value);
            }
            return $out;
        }

        return trim(preg_replace('/<[^>]*>/', '', $in));
    }
}
