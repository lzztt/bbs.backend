<?php declare(strict_types=1);

namespace site;

class Config
{
    const STAGE_DEVELOPMENT = 0;
    const STAGE_PRODUCTION = 1;

    const CITIES = [
        'houstonbbs.com' => [
            'id' => 1,
            'name' => '休斯顿',
            'tidForum' => 1,
            'tidYp' => 2,
        ],
        'dallasbbs.com' => [
            'id' => 2,
            'name' => '达拉斯',
            'tidForum' => 127,
        ],
        'austinbbs.com' => [
            'id' => 3,
            'name' => '奥斯汀',
            'tidForum' => 160,
        ]
    ];

    public $stage;
    public $cache;
    public $path;
    public $db;
    public $getkeys;
    public $theme;
    public $domain;
    public $webmaster;
    public $image;

    private function __construct(string $serverName)
    {
        $this->domain = implode('.', array_slice(explode('.', $serverName), -2));

        $this->stage = self::STAGE_DEVELOPMENT;
        //$this->stage = self::STAGE_PRODUCTION;
        if (array_key_exists($this->domain, self::CITIES)) {
            $this->stage = self::STAGE_PRODUCTION;
        }

        $this->city = $this->getCity($serverName);

        $this->webmaster = 'mikalotus3355@gmail.com';

        $this->path = [
            'server' => __DIR__,
            'theme' => __DIR__ . '/theme',
            'site' => dirname(__DIR__),
            'log' => dirname(__DIR__) . '/log',
            'file' => dirname(__DIR__) . '/client',
            'backup' => dirname(__DIR__) . '/backup',
            'cache' => '/tmp/' . $serverName,
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

    private function getCity(string $serverName): object
    {
        $uriName = preg_replace(['/\w*\./', '/bbs.*/'], '', $serverName, 1);
        $city = (object) self::CITIES[$uriName . 'bbs.com'];
        $city->uriName = $uriName;
        return $city;
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
