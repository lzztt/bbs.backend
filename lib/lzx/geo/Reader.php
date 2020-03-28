<?php declare(strict_types=1);

namespace lzx\geo;

use Exception;
use lzx\geo\Geo;
use lzx\geo\Name;
use MaxMind\Db\Reader as MaxMindReader;

class Reader
{
    private const DB_FILE = '/var/lib/GeoIP/GeoLite2-City.mmdb';
    private const LANG_ZH = 'zh-CN';
    private const LANG_EN = 'en';
    private const CHINA_ZH = '中国';
    private const CHINA_EN = 'China';

    private static $CHINA_REGIONS = [
        'Hong Kong' => '香港',
        'Macao' => '澳门',
        'Taiwan' => '台湾'
    ];

    private $reader;

    private function __construct()
    {
        $this->reader = new MaxMindReader(self::DB_FILE);
    }

    public static function getInstance(): Reader
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new self();
        }
        return $instance;
    }

    public function get(string $ip): Geo
    {
        static $cache = [];
        
        if (strlen($ip) === 4) {
            $key = $ip;
            $ip = inet_ntop($ip);
            if ($ip === false) {
                return Geo::getEmpty();
            }
        } else {
            $key = inet_pton($ip);
            if ($key === false) {
                return Geo::getEmpty();
            }
        }

        if (filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false) {
            return Geo::getEmpty();
        }

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        try {
            $r = $this->reader->get($ip);
        } catch (Exception $e) {
            return Geo::getEmpty();
        }

        $name = $this->findChinaRegion($r['country']['names']);
        if ($name) {
            $country = new Name(self::CHINA_EN, self::CHINA_ZH);
            $region = new Name($name, self::$CHINA_REGIONS[$name]);
        } else {
            $country = new Name(
                (string) $r['country']['names'][self::LANG_EN],
                (string) $r['country']['names'][self::LANG_ZH]
            );
            $region = new Name(
                (string) $r['subdivisions'][0]['names'][self::LANG_EN],
                (string) $r['subdivisions'][0]['names'][self::LANG_ZH]
            );
        }

        $city = new Name(
            (string) $r['city']['names'][self::LANG_EN],
            (string) $r['city']['names'][self::LANG_ZH]
        );

        $continent = new Name(
            (string) $r['continent']['names'][self::LANG_EN],
            (string) $r['continent']['names'][self::LANG_ZH]
        );

        $geo = new Geo($city, $region, $country, $continent);

        $cache[$key] = $geo;
        return $geo;
    }

    private function findChinaRegion(array $names): ?string
    {
        $key = $names[self::LANG_EN];
        // check keys
        if (array_key_exists($key, self::$CHINA_REGIONS)) {
            return $key;
        } else {
            // check values
            $key = array_search($names[self::LANG_ZH], self::$CHINA_REGIONS);
            return $key ? $key : null;
        }
    }
}