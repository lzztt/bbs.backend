<?php declare(strict_types=1);

namespace site;

class Config
{
    const STAGE_DEVELOPMENT = 0;
    const STAGE_PRODUCTION = 1;

    public $stage;
    public $cache;
    public $path;
    public $db;
    public $getkeys;
    public $theme;
    public $domain;
    public $webmaster;
    public $image;

    private function __construct(string $server_name)
    {
        $this->domain = implode('.', array_slice(explode('.', $server_name), -2));

        $this->stage = self::STAGE_DEVELOPMENT;
        //$this->stage = self::STAGE_PRODUCTION;
        if (in_array($this->domain, ['houstonbbs.com', 'dallasbbs.com', 'austinbbs.com'])) {
            $this->stage = self::STAGE_PRODUCTION;
        }

        $this->webmaster = 'mikalotus3355@gmail.com';

        $this->path = [
            'server' => __DIR__,
            'theme' => __DIR__ . '/theme',
            'site' => dirname(__DIR__),
            'log' => dirname(__DIR__) . '/log',
            'file' => dirname(__DIR__) . '/client',
            'backup' => dirname(__DIR__) . '/backup',
            'cache' => '/tmp/' . $server_name,
        ];
        $this->cache = true;
        $this->db = [
            'dsn' => 'hbbs',
            'user' => 'web',
            'password' => 'Ab663067',
        ];
        $this->getkeys = ['p', 'r', 'u', 'c', 't', 'action'];
        $this->theme = 'roselife';

        $this->image = [
            'types' => [IMAGETYPE_GIF, IMAGETYPE_PNG, IMAGETYPE_JPEG],
            'height' => 9000,
            'width' => 600,
            'size' => 5242880,
        ];
    }

    public static function getInstance(): Config
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new self($_SERVER['SERVER_NAME']);
        }
        return $instance;
    }
}
