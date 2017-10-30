<?php

namespace lzx\core;

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
    public $language;
    public $timestamp;

    private function __construct()
    {
        $this->domain = $_SERVER['HTTP_HOST'];
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->uri = strtolower($_SERVER['REQUEST_URI']);

        $this->timestamp = intval($_SERVER['REQUEST_TIME']);

        $this->post = $this->toUTF8($_POST);
        $this->get = $this->toUTF8($_GET);
        $this->files = $this->getUploadFiles();
        $this->json = $this->decodeJsonPost();

        $arr = explode($this->domain, $_SERVER['HTTP_REFERER']);
        $this->referer = sizeof($arr) > 1 ? $arr[1] : null;
    }

    /**
     *
     * @staticvar self $instance
     * @return \lzx\core\Request
     */
    public static function getInstance()
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new self();
        }
        return $instance;
    }

    /*
     * build a list of uploaded files
     */

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

    private function decodeJsonPost()
    {
        $json = null;
        $data = file_get_contents('php://input');
        if (!empty($data)) {
            $json = json_decode($data, true);
        }

        return $json;
    }

    /**
     *
     * @param string $uri test
     * @return NULL
     */
    public function getURIargs($uri)
    {
        static $URIargs = [];

        if (!array_key_exists($uri, $URIargs)) {
            $arg = trim(strtok($uri, '?'), ' /');
            $URIargs[$uri] = empty($arg) ? [] : explode('/', $arg);
        }

        return $URIargs[$uri];
    }

    public function buildURI(array $args = [], array $get = [])
    {
        $query = [];
        foreach ($get as $k => $v) {
            $query[] = $k . '=' . $v;
        }

        return '/' . implode('/', $args) . ($query ? '?' . implode('&', $query) : '');
    }

    public function curlGetData($url)
    {
        $c = curl_init($url);
        curl_setopt_array($c, [
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_CONNECTTIMEOUT => 2,
            \CURLOPT_TIMEOUT          => 3
        ]);
        $data = curl_exec($c);
        curl_close($c);

        return $data; // will return FALSE on failure
    }

    public function getCityFromIP($ip)
    {
        static $cities = [];

        // return from cache;
        if (array_key_exists($ip, $cities)) {
            return $cities[$ip];
        }

        // get city from geoip database
        $city = 'N/A';
        try {
            if (is_null($ip)) {
                return $city;
            }

            $ip = inet_ntop($ip);
            if ($ip === false) {
                return $city;
            }

            $geo = geoip_record_by_name($ip);

            if ($geo['city']) {
                $city = $geo['city'];
            }
        } catch (\Exception $e) {
            return 'UNKNOWN';
        }

        // save city to cache
        $cities[$ip] = $city;

        return $city;
    }

    public function getLocationFromIP($ip)
    {
        $location = 'N/A';

        try {
            if (is_null($ip)) {
                return $location;
            }

            $ip = inet_ntop($ip);
            if ($ip === false) {
                return $location;
            }

            $city = 'N/A';
            $region = 'N/A';
            $country = 'N/A';

            $geo = geoip_record_by_name($ip);

            if ($geo['city']) {
                $city = $geo['city'];
            }

            if ($geo['country_name']) {
                $country = $geo['country_name'];
            }

            if ($geo['region'] && $geo['country_code']) {
                $region = geoip_region_name_by_code($geo['country_code'], $geo['region']);
            }

            $location = $city . ', ' . $region . ', ' . $country;
        } catch (\Exception $e) {
            return 'UNKNOWN';
        }

        return $location;
    }

    private function toUTF8($in)
    {
        if (is_array($in)) {
            $out = [];
            foreach ($in as $key => $value) {
                $out[$this->toUTF8($key)] = $this->toUTF8($value);
            }
            return $out;
        }

        if (is_string($in) && !mb_check_encoding($in, "UTF-8")) { // user input data is trimed and cleaned here, escapte html tags
            return \utf8_encode($in);
            //return utf8_encode(trim(preg_replace('/<[^>]*>/', '', $in)));
            //to trim all tags: preg_replace('/<[^>]*>/', '',  trim($in))
            //to escape tags: str_replace(['<', '>'), ['&lt;', '&gt;'), trim($in))
        }

        return trim(preg_replace('/<[^>]*>/', '', $in));
    }
}

//__END_OF_FILE__
