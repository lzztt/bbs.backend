<?php

declare(strict_types=1);

namespace lzx\core;

use lzx\geo\Reader;

trait UtilTrait
{
    protected static function curlGet(string $url): string
    {
        $c = curl_init($url);
        curl_setopt_array($c, [
            CURLOPT_USERAGENT => 'curl',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => 'gzip,deflate',
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 3
        ]);
        $data = curl_exec($c);
        curl_close($c);

        return $data ? $data : '';
    }

    protected static function getLocationFromIp(string $ip, bool $fullInfo = true): string
    {

        $geo = Reader::getInstance()->get($ip);
        return $fullInfo ? $geo->getCityWithRegion() : $geo->getCity();
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
}
