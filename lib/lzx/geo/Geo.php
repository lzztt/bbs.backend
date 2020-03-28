<?php declare(strict_types=1);

namespace lzx\geo;

use lzx\geo\Name;

class Geo
{
    const UNKNOWN_CITY = '未知城市';
    private const ASIA_EN = 'Asia';
    private const CHINA_EN = 'China';

    public $city;
    public $region;
    public $country;
    public $continent;

    public static function getEmpty(): Geo
    {
        $empty = new Name('', '');
        return new self($empty, $empty, $empty, $empty);
    }

    public function __construct(Name $city, Name $region, Name $country, Name $continent)
    {
        $this->city = $city;
        $this->region = $region;
        $this->country = $country;
        $this->continent = $continent;
    }

    public function getCity(): string
    {
        $city = $this->getCityWithLang();

        if ($city) {
            return $city;
        }

        if ($this->region->zh) {
            return $this->region->zh;
        }

        if ($this->country->zh) {
            return $this->country->zh;
        }

        return self::UNKNOWN_CITY;
    }

    public function getCityWithRegion(): string
    {
        $region = $this->country->zh . ' ' . $this->region->zh;
        if ($region === ' ') {
            return self::UNKNOWN_CITY;
        }

        $city = $this->getCityWithLang();

        if ($city) {
            return $city . ' (' . $region . ')';
        }

        return $region;
    }

    private function getCityWithLang(): string
    {
        if ($this->continent->en === self::ASIA_EN) {
            $city = $this->city->zh;
        }
        if (!$city && $this->country->en !== self::CHINA_EN) {
            $city = $this->city->en;
        }
        return $city;
    }
}