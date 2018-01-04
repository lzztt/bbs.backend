<?php declare(strict_types=1);

namespace lzx\core;

use Exception;

trait UtilTrait
{
    protected static function curlGetData(string $url): string
    {
        $c = curl_init($url);
        curl_setopt_array($c, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 3
        ]);
        $data = curl_exec($c);
        curl_close($c);

        return $data ? $data : '';
    }

    protected static function getCityFromIP(string $ip): string
    {
        static $cities = [];

        $city = 'N/A';
        if (!$ip) {
            return $city;
        }

        // return from cache;
        if (array_key_exists($ip, $cities)) {
            return $cities[$ip];
        }

        // get city from geoip database
        try {
            $ip = inet_ntop($ip);
            if ($ip === false) {
                return $city;
            }

            $geo = geoip_record_by_name($ip);

            if ($geo['city']) {
                $city = self::encode($geo['city'], mb_internal_encoding());
            }
        } catch (Exception $e) {
            return 'UNKNOWN';
        }

        // save city to cache
        $cities[$ip] = $city;

        return $city;
    }

    protected static function getLocationFromIP(string $ip): string
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

            $encoding = mb_internal_encoding();
            $location = self::encode($city, $encoding) . ', ' .
                    self::encode($region, $encoding) . ', ' .
                    self::encode($country, $encoding);
        } catch (Exception $e) {
            return 'UNKNOWN';
        }

        return $location;
    }

    private static function encode(string $str, string $toEncoding): string
    {
        $fromEncode = mb_detect_encoding($str, 'UTF-8,ASCII,ISO-8859-1,UTF-7,EUC-JP,SJIS,eucJP-win,SJIS-win,JIS,ISO-2022-JP');
        if ($fromEncode === 'UTF-8') {
            return $str;
        } elseif ($fromEncode === false) {
            return 'N/A';
        } else {
            return mb_convert_encoding($str, $toEncoding, $fromEncode);
        }
    }

    protected function getPagerInfo(int $nTotal, int $nPerPage): array
    {
        if ($nPerPage <= 0) {
            throw new Exception('invalid value for number of items per page: ' . $nPerPage);
        }

        $pageCount = $nTotal > 0 ? (int) ceil($nTotal / $nPerPage) : 1;
        if ($this->request->get['p']) {
            if ($this->request->get['p'] === 'l') {
                $pageNo = $pageCount;
            } elseif (is_numeric($this->request->get['p'])) {
                $pageNo = (int) $this->request->get['p'];

                if ($pageNo < 1 || $pageNo > $pageCount) {
                    $this->pageNotFound();
                }
            } else {
                $this->pageNotFound();
            }
        } else {
            $pageNo = 1;
        }

        return [$pageNo, $pageCount];
    }
}
