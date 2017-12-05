<?php declare(strict_types=1);

namespace site;

class Config
{
    const STAGE_DEVELOPMENT = 0;
    const STAGE_TESTING = 1;
    const STAGE_PRODUCTION = 2;
    const MODE_OFFLINE = 0;
    const MODE_READONLY = 1;
    const MODE_FULL = 2;

    public $stage;
    public $mode;
    public $cache;
    public $path;
    public $db;
    public $getkeys;
    public $language;
    public $theme;
    public $domain;
    public $webmaster;
    public $image;

    private function __construct()
    {
        $this->stage = self::STAGE_DEVELOPMENT;
        //$this->stage = self::STAGE_PRODUCTION;
        $this->mode = self::MODE_FULL;

        $this->path = [
            'server' => __DIR__,
            'theme' => __DIR__ . '/theme',
            'site' => dirname(__DIR__),
            'log' => dirname(__DIR__) . '/log',
            'file' => dirname(__DIR__) . '/client',
            'backup' => dirname(__DIR__) . '/backup',
            'cache' => '/cache/' . $_SERVER['SERVER_NAME'], //note: nginx webserver also use $server_name as the cache path
        ];
        $this->cache = false;
        $this->db = [
            'dsn' => 'hbbs',
            'user' => 'web',
            'password' => 'Ab663067',
        ];
        $this->getkeys = ['p', 'r', 'u', 'c', 't', 'action'];
        $this->language = 'zh-cn';
        $this->theme = [
            'roselife' => 'roselife',
        ];
        $this->domain = implode('.', array_slice(explode('.', $_SERVER['HTTP_HOST']), -2));
        $this->webmaster = 'mikalotus3355@gmail.com';

        $this->image = [
            'types' => [IMAGETYPE_GIF, IMAGETYPE_PNG, IMAGETYPE_JPEG],
            'height' => 9000,
            'width' => 600,
            'size' => 5242880
        ];

        // make this file immutable
        // root# chattr +i config.php
        // just in case we rsync the dev/testing configuration file to production
        if (in_array($this->domain, ['houstonbbs.com', 'dallasbbs.com', 'austinbbs.com'])) {
            $this->stage = self::STAGE_PRODUCTION;
        }
    }

    public static function getInstance()
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new self();
        }
        return $instance;
    }
}
