<?php declare(strict_types=1);

namespace lzx\core;

use Exception;

trait UtilTrait
{
    protected static function curlGet(string $url): string
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

    protected static function getLocationFromIp(string $ip, bool $fullInfo = true): string
    {
        static $cache = [];

        if (array_key_exists($ip, $cache)) {
            goto done;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) === false) {
            try {
                $ip = inet_ntop($ip);
            } catch (Exception $e) {
                $cache[$ip] = ['UNKNOWN'];
                goto done;
            }
            if ($ip === false) {
                $cache[$ip] = ['UNKNOWN'];
                goto done;
            }
        }

        $geo = geoip_record_by_name($ip);
        if ($geo === false) {
            $cache[$ip] = ['UNKNOWN'];
            goto done;
        }

        $encoding = mb_internal_encoding();

        $city = $geo['city'] ? self::encode($geo['city'], $encoding) : 'N/A';
        $country = $geo['country_name'] ? self::encode($geo['country_name'], $encoding) : 'N/A';

        if ($geo['region'] && $geo['country_code']) {
            $region = geoip_region_name_by_code($geo['country_code'], $geo['region']);
        }
        $region = $region ? self::encode($region, $encoding) : 'N/A';

        $cache[$ip] = [$city, $region, $country];

        done:
        return $fullInfo ? implode(', ', $cache[$ip]) : $cache[$ip][0];
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
