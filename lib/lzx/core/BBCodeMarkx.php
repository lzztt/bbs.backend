<?php

namespace lzx\core;

// convert bbcode to markx syntax

class BBCodeMarkx
{

   private static function _bbcode_ulist( $s )
   {
      // '/\[list\](.*?)\[\/list\]/ms'
      return preg_replace( '/\[\*\]\s?/ms', '- ', $s[ 1 ] );
   }

   private static function _bbcode_olist( $s )
   {
      return preg_replace( '/\[\*\]\s?/ms', '1. ', $s[ 2 ] );
   }

   private static function _quote( $text )
   {
      return "\n> " . str_replace( "\n", "\n> ", trim( preg_replace( ['/\[quote.*\[\/quote\]/ms', '/\n{2,}/ms' ], ['', "\n" ], $text ) ) );
   }

   private static function _bbcode_quote( $s )
   {
      $text = $s[ 1 ];
      //[quote] in /[quote(.*)\[\/quote\]/ms
      if ( $text[ 0 ] == ']' )
      {
         return self::_quote( substr( $text, 1 ) );
      }
      //[quote="name"] in /[quote(.*)\[\/quote\]/ms
      if ( $text[ 0 ] == '=' )
      {
         $p = strpos( $text, ']' );
         return "\n> [g *" . trim( substr( $text, 1, $p - 1 ), ' \'"' ) . " :*]" . self::_quote( substr( $text, $p + 1 ) );
      }
   }

   public static function parse( $text )
   {
      if ( \strpos( $text, '[/' ) === FALSE )
      {
         // if no colose tag, don't borther
         return $text;
      }

      // BBCode [code]
      //$text = preg_replace_callback( '/\[code\](.*?)\[\/code\]/ms', [self, '_bbcode_escape' ], $text );
      $text = str_replace( ['[code]', '[/code]' ], ['`', '`' ], $text );
      $text = preg_replace_callback( '/\[list\](.*?)\[\/list\]/ms', [self, '_bbcode_ulist' ], $text );
      $text = preg_replace_callback( '/\[list\=(.*?)\](.*?)\[\/list\]/ms', [self, '_bbcode_olist' ], $text );
      $text = preg_replace_callback( '/\[quote(.*)\[\/quote\]/ms', [self, '_bbcode_quote' ], $text );


      // Smileys to find...
      // Add closing tags to prevent users from disruping your site's HTML
      // (required for nestable tags only: [list] and [quote])
      /*
        $unclosed = ((int) preg_match_all( '/\[quote\="?(.*?)"?\]/ms', $text, $matches )) + substr_count( $text, '[quote]' ) - substr_count( $text, '[/quote]' );

        if ( $unclosed < 0 )
        $text = str_repeat( '[quote]', (-$unclosed ) ) . $text;
        if ( $unclosed > 0 )
        $text = $text . str_repeat( '[/quote]', $unclosed );
       */

      /*
        for ($i = 0; $i < (substr_count($text, '[list') - substr_count($text, '[/list]')); $i++)
        {
        $text .= '[/list]';
        }
       *         
       * '/\[quote\="?(.*?)"?\](.*?)\[\/quote\]/ms'
       * '/\[quote\](.*?)\[\/quote\]/ms'
        => '<div class="quote"><b>\1 wrote:</b><blockquote class="quote-body">',
        '/\[list\=(.*?)\](.*?)\[\/list\]/ms'
        => '<ol start="\1">\2</ol>',
        '/\[list\](.*?)\[\/list\]/ms'
        => '<ul>\1</ul>',
        '/\[\*\]\s?(.*?)\n/ms'
        => '<li>\1</li>',
       * 
       */

      //$text = str_replace( ['[quote]', '[/quote]' ], ['<div class="quote">Quote:<blockquote class="quote-body">', '</blockquote></div>' ], $text );
      // BBCode to find...
      $bbcode = [
         '/\[b\](.*?)\[\/b\]/ms'                        => '*\1*',
         '/\[i\](.*?)\[\/i\]/ms'                        => '\1',
         '/\[u\](.*?)\[\/u\]/ms'                        => '_\1_',
         '/\[s\](.*?)\[\/s\]/ms'                        => '~\1~',
         '/\[img\="?(.*?)"?\](.*?)\[\/img\]/ms'         => '\2[\1]',
         '/\[img\](.*?)\[\/img\]/ms'                    => '\1',
         '/\[url\="?(.*?)"?\](.*?)\[\/url\]/ms'         => '\1[\2]',
         '/\[url](.*?)\[\/url\]/ms'                     => '\1',
         '/\[size\=120%\](.*?)\[\/size\]/ms'            => '*\1*',
         '/\[color\="?(.*?)"?\](.*?)\[\/color\]/ms'     => '[b \2]',
         '/\[bgcolor\="?(.*?)"?\](.*?)\[\/bgcolor\]/ms' => '[!g \2]',
         '/\[youtube\](.*?)\[\/youtube\]/ms'            => 'https://www.youtube.com/watch?v=\1'
      ];

      $text = preg_replace( array_keys( $bbcode ), array_values( $bbcode ), $text );

      // paragraphs
      //$text = str_replace("\r", "", $text);
      //$text = "<p>" . preg_replace("/(\n){2,}/", "</p><p>", $text) . "</p>";
      //$text = nl2br( $text );
      //$text = preg_replace_callback( '/<pre>(.*?)<\/pre>/ms', [self, '_bbcode_removeBr'], $text );
      //$text = preg_replace('/<p><pre>(.*?)<\/pre><\/p>/ms', "<pre>\\1</pre>", $text);
      //$text = preg_replace_callback( '/<ul>(.*?)<\/ul>/ms', [self, '_bbcode_removeBr'], $text );
      //$text = preg_replace('/<p><ul>(.*?)<\/ul><\/p>/ms', "<ul>\\1</ul>", $text);
      // matches an "xxxx://yyyy" URL at the start of a line, or after a space.
      // xxxx can only be alpha characters.
      // yyyy is anything up to the first space, newline, comma, double quote or <
      //$text = preg_replace( '#(?<=^|[\t\r\n >\(\[\]\|])([a-z]+?://[\w\-]+\.([\w\-]+\.)*\w+(:[0-9]+)?(/[^ "\'\(\n\r\t<\)\[\]\|]*)?)((?<![,\.])|(?!\s))#i', '<a href="\1">\1</a>', $text );

      return $text;
   }

}

//__END_OF_FILE__