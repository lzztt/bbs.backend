<?php declare(strict_types=1);

namespace lzx\core;

use Exception;

trait UtilTrait
{
    protected static function curlGetData($url)
    {
        $c = curl_init($url);
        curl_setopt_array($c, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 3
        ]);
        $data = curl_exec($c);
        curl_close($c);

        return $data; // will return FALSE on failure
    }

    protected static function getCityFromIP($ip)
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
        } catch (Exception $e) {
            return 'UNKNOWN';
        }

        // save city to cache
        $cities[$ip] = $city;

        return $city;
    }

    protected static function getLocationFromIP($ip)
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
        } catch (Exception $e) {
            return 'UNKNOWN';
        }

        return $location;
    }
}
