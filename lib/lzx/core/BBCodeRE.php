<?php

declare(strict_types=1);

namespace lzx\core;

class BBCodeRE
{
    const BBCODE = [
        '/\[youtube\](.*?)\[\/youtube\]/ms'
        => '<iframe class="youtube" src="//www.youtube.com/embed/\1" frameborder="0" allowfullscreen></iframe>',
    ];

    private static function escape(array $matches): string
    {
        $code = $matches[1];
        $code = str_replace(['[', ']'], ['&#91;', '&#93;'], $code);
        return '```' . PHP_EOL . $code . PHP_EOL . '```';
    }

    public static function parse(string $text): string
    {
        if (strpos($text, '[/') === false) { // if no colse tag, don't borther
            return $text;
        }

        // BBCode [code]
        $text = preg_replace_callback('/\[code\](.*?)\[\/code\]/ms', [__CLASS__, 'escape'], $text);

        $text = preg_replace(array_keys(self::BBCODE), array_values(self::BBCODE), $text);

        return $text;
    }
}
