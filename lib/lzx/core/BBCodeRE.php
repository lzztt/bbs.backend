<?php

declare(strict_types=1);

namespace lzx\core;

class BBCodeRE
{
    const BBCODE = [
        '/\[b\](.*?)\[\/b\]/ms'
        => '<strong>\1</strong>',
        '/\[i\](.*?)\[\/i\]/ms'
        => '<em>\1</em>',
        '/\[u\](.*?)\[\/u\]/ms'
        => '<span style="text-decoration:underline">\1</span>',
        '/\[s\](.*?)\[\/s\]/ms'
        => '<del>\1</del>',
        '/\[img\="?(.*?)"?\](.*?)\[\/img\]/ms'
        => '<img class="bb_image" src="\2" alt="\2">',
        '/\[img\](.*?)\[\/img\]/ms'
        => '<img class="bb_image" src="\1" alt="\1">',
        '/\[url\="?(.*?)"?\](.*?)\[\/url\]/ms'
        => '<a rel="nofollow" href="\1">\2</a>',
        '/\[url](.*?)\[\/url\]/ms'
        => '<a rel="nofollow" href="\1">\1</a>',
        '/\[size\="?(.*?)"?\](.*?)\[\/size\]/ms'
        => '<span style="font-size:\1">\2</span>',
        '/\[color\="?(.*?)"?\](.*?)\[\/color\]/ms'
        => '<span style="color:\1">\2</span>',
        '/\[bgcolor\="?(.*?)"?\](.*?)\[\/bgcolor\]/ms'
        => '<span style="background-color:\1">\2</span>',
        '/\[quote\="?(.*?)"?\]/ms'
        => '<blockquote data-author="\1 :">',
        '/\[list\=(.*?)\](.*?)\[\/list\]/ms'
        => '<ol start="\1">\2</ol>',
        '/\[list\](.*?)\[\/list\]/ms'
        => '<ul>\1</ul>',
        '/\[\*\]\s?(.*?)\n/ms'
        => '<li>\1</li>',
        '/\[youtube\](.*?)\[\/youtube\]/ms'
        => '<iframe class="youtube" src="//www.youtube.com/embed/\1" frameborder="0" allowfullscreen></iframe>',
    ];

    private static function escape(array $matches): string
    {
        $code = $matches[1];
        $code = str_replace(['[', ']'], ['&#91;', '&#93;'], $code);
        return '<code class="code">' . $code . '</code>';
    }

    private static function removeBr(array $matches): string
    {
        return str_replace('<br />', '', $matches[0]);
    }

    public static function parse(string $text): string
    {
        if (strpos($text, '[/') === false) { // if no colse tag, don't borther
            $text = preg_replace('#(?<=^|[\t\r\n >\(\[\]\|])(https?://[\w\-]+\.([\w\-]+\.)*\w+(:[0-9]+)?(/[^ "\'\(\n\r\t<\)\[\]\|]*)?)((?<![,\.])|(?!\s))#i', '<a href="\1">\1</a>', $text);
            return nl2br($text);
        }

        // BBCode [code]
        $text = preg_replace_callback('/\[code\](.*?)\[\/code\]/ms', [__CLASS__, 'escape'], $text);

        $unclosed = ((int) preg_match_all('/\[quote\="?(.*?)"?\]/ms', $text, $matches)) + substr_count($text, '[quote]') - substr_count($text, '[/quote]');

        if ($unclosed < 0) {
            $text = str_repeat('[quote]', (-$unclosed)) . $text;
        }
        if ($unclosed > 0) {
            $text = $text . str_repeat('[/quote]', $unclosed);
        }

        $text = str_replace(['[quote]', '[/quote]'], ['<blockquote>', '</blockquote>'], $text);

        $text = preg_replace(array_keys(self::BBCODE), array_values(self::BBCODE), $text);

        $text = nl2br($text);

        $text = preg_replace_callback('/<pre>(.*?)<\/pre>/ms', [__CLASS__, 'removeBr'], $text);

        $text = preg_replace_callback('/<ul>(.*?)<\/ul>/ms', [__CLASS__, 'removeBr'], $text);

        // matches an "xxxx://yyyy" URL at the start of a line, or after a space.
        // xxxx can only be alpha characters.
        // yyyy is anything up to the first space, newline, comma, double quote or <
        $text = preg_replace('#(?<=^|[\t\r\n >\(\[\]\|])([a-z]+?://[\w\-]+\.([\w\-]+\.)*\w+(:[0-9]+)?(/[^ "\'\(\n\r\t<\)\[\]\|]*)?)((?<![,\.])|(?!\s))#i', '<a href="\1">\1</a>', $text);

        return $text;
    }
}
