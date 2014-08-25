<?php

namespace lzx\core;

// from drupal bbcode module: 6.x-1.2 	tar.gz (40.06 KB) | zip (52.83 KB) 	2008-Nov-30
// $Id: bbcode-filter.inc,v 1.66 2008/11/30 08:50:08 naudefj Exp $

class BBCode
{

   public static function parse( $text )
   {
      static $bbc = [
         'code' => ['code' => ['type' => BBCODE_TYPE_NOARG, 'open_tag' => '<code class="code">', 'close_tag' => '</code>', 'content_handling' => [__CLASS__, '_escape' ] ] ],
         'tags' => [
            'b' => ['type' => BBCODE_TYPE_NOARG, 'open_tag' => '<strong>', 'close_tag' => '</strong>' ],
            'i' => ['type' => BBCODE_TYPE_NOARG, 'open_tag' => '<em>', 'close_tag' => '</em>' ],
            'u' => ['type' => BBCODE_TYPE_NOARG, 'open_tag' => '<span style="text-decoration:underline">', 'close_tag' => '</span>' ],
            's' => ['type' => BBCODE_TYPE_NOARG, 'open_tag' => '<del>', 'close_tag' => '</del>' ],
            'img' => ['type' => BBCODE_TYPE_OPTARG, 'open_tag' => '', 'close_tag' => '', 'default_arg' => '{CONTENT}', 'content_handling' => [__CLASS__, '_img' ] ],
            'url' => ['type' => BBCODE_TYPE_OPTARG, 'open_tag' => '<a href="{PARAM}">', 'close_tag' => '</a>', 'default_arg' => '{CONTENT}' ],
            'size' => ['type' => BBCODE_TYPE_ARG, 'open_tag' => '<span style="font-size:{PARAM}">', 'close_tag' => '</span>' ],
            'color' => ['type' => BBCODE_TYPE_ARG, 'open_tag' => '<span style="color:{PARAM}">', 'close_tag' => '</span>' ],
            'bgcolor' => ['type' => BBCODE_TYPE_ARG, 'open_tag' => '<span style="background-color:{PARAM}">', 'close_tag' => '</span>' ],
            'quote' => ['type' => BBCODE_TYPE_ARG, 'open_tag' => '<blockquote data-author="{PARAM} :">', 'close_tag' => '</blockquote>' ],
            'list' => ['type' => BBCODE_TYPE_OPTARG, 'open_tag' => '', 'close_tag' => '', 'default_arg' => '', 'content_handling' => [__CLASS__, '_list' ] ],
            'youtube' => ['type' => BBCODE_TYPE_NOARG, 'open_tag' => '', 'close_tag' => '', 'content_handling' => [__CLASS__, '_youtube' ] ],
            'googlemap' => ['type' => BBCODE_TYPE_NOARG, 'open_tag' => '', 'close_tag' => '', 'content_handling' => [__CLASS__, '_googlemap' ] ],
         ]
      ];

      // convert url string to links
      $text = \preg_replace( '#(?<=^|[\t\r\n >\(\[\]\|])(https?://[\w\-]+\.([\w\-]+\.)*\w+(:[0-9]+)?(/[^ "\'\(\n\r\t<\)\[\]\|]*)?)((?<![,\.])|(?!\s))#i', '<a href="\1">\1</a>', $text );

      // only process bbcode when seeing a colse tag
      if ( \strpos( $text, '[/' ) !== FALSE )
      {
         // process [code] tag
         if ( \strpos( $text, '[code]' ) !== FALSE )
         {
            $text = \bbcode_parse( \bbcode_create( $bbc[ 'code' ] ), $text );
         }

         // process other BBCode tags
         $text = \bbcode_parse( \bbcode_create( $bbc[ 'tags' ] ), $text );
      }

      // convert new line to <br> except blockquote, iframe, ul, ol
      return \str_replace( [ "e>\n", "e>\r\n", "l>\n", "l>\r\n", "\n", "\r\n" ], [ 'e>', 'e>', "l>", "l>", '<br>', '<br>' ], $text );
   }

   private static function _escape( $content, $param )
   {
      return \str_replace( ['[', ']' ], ['&#91;', '&#93;' ], $content );
   }

   private static function _img( $content, $param )
   {
      return '<img class="bb_image" src="' . $content . '" alt="' . ($param == '{CONTENT}' ? $content : $param) . '">';
   }

   private static function _list( $content, $param )
   {
      $li = '';
      foreach ( \explode( '[*]', $content ) as $i )
      {
         $t = \trim( $i );
         if ( \strlen( $t ) )
         {
            $li = $li . '<li>' . $t . '</li>';
         }
      }

      if ( empty( $param ) )
      {
         return '<ul>' . $li . '</ul>';
      }
      else
      {
         return '<ol start="' . $param . '">' . $li . '</ol>';
      }
   }

   private static function _youtube( $content, $param )
   {
      return '<iframe width="480" height="360" src="http://www.youtube.com/embed/' . $content . '" frameborder="0" allowfullscreen></iframe>';
   }

   private static function _googlemap( $content, $param )
   {
      return '<iframe width="425" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="' . $content . '&f=d&source=s_d&output=embed"></iframe><br /><small><a href="' . $content . '" style="text-align:left">View Larger Map</a></small>';
   }

}

//__END_OF_FILE__