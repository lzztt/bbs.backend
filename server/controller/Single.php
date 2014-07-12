<?php

namespace site\controller;

use site\Controller;
use lzx\core\Request;
use lzx\html\Template;
use site\Config;
use lzx\core\Logger;
use lzx\core\Cache;
use lzx\core\Session;
use lzx\core\Cookie;
use site\dbobject\FFComment;
use lzx\db\DB;

/**
 * @property \lzx\db\DB $db database object
 */
abstract class Single extends Controller
{

   protected $tea_start = '2011-08-21';
   protected $rich_start = '2012-03-11';
   protected $thirty_start = '2013-03-19';
   protected $thirty_two_start = '2013-08-16';
   protected $db;

   public function __construct( Request $req, Template $html, Config $config, Logger $logger, Cache $cache, Session $session, Cookie $cookie )
   {
      parent::__construct( $req, $html, $config, $logger, $cache, $session, $cookie );
      // don't cache user page at page level
      $this->cache->setStatus( FALSE );

      $this->db = DB::getInstance();

      $this->tea_start = \strtotime( $this->tea_start );
      $this->rich_start = \strtotime( $this->rich_start );
      $this->thirty_start = \strtotime( $this->thirty_start );
      $this->thirty_two_start = \strtotime( $this->thirty_two_start );
   }

   /**
    * 
    * observer interface
    */
   public function update( \SplSubject $html )
   {
      $html->tpl = 'FindYouFindMe';

      $html->var[ 'header' ] = new Template( 'FFpage_header' );
      $html->var[ 'footer' ] = $this->_footer();
   }


   /**
    * 
    * protected methods
    */
   protected function _getImageSlider()
   {
      $images = [
         [
            'path' => '/data/fyfm/13118198981266.png',
            'name' => '薄荷'
         ],
         [
            'path' => '/data/fyfm/13118268141266.png',
            'name' => '七夕'
         ],
         [
            'path' => '/data/fyfm/13118224061268.png',
            'name' => '吉娃莲'
         ],
         [
            'path' => '/data/fyfm/13119630681266.png',
            'name' => '凡凡觅友'
         ],
      ];

      \shuffle( $images );

      $content[ 'images' ] = $images;
      return new Template( 'image_slider', $content );
   }

   protected function _addComment()
   {
      if ( strlen( $this->request->post[ 'comment' ] ) < 5 )
      {
         return '<span style="color:#B22222">错误</span>: 留言字数最少为5个字符';
      }


      $comment = new FFComment();

      if ( $this->request->post[ 'anonymous' ] || empty( $this->request->post[ 'name' ] ) )
      {
         $comment->name = $this->request->ip;
      }
      else
      {
         $comment->name = $this->request->post[ 'name' ];
      }

      $comment->body = $this->request->post[ 'comment' ];
      $comment->time = $this->request->timestamp;
      $comment->add();

      $output = '谢谢您的留言，请点击这里<a class="commentViewButton" style="color:#A0522D" href="#">查看所有留言</a>'
         . '<script type="text/javascript">$("#footer").load("/single/footer");</script>';
      return $output;
   }

   protected function _viewComment()
   {
      $db = $this->db;
      $comments = $db->query( 'CALL get_attendee_comments_single(' . $this->thirty_two_start . ',' . $this->request->timestamp . ')' );
      $comments_thirty = $db->query( 'CALL get_attendee_comments_single(' . $this->thirty_start . ',' . $this->thirty_two_start . ')' );
      $comments_rich = $db->query( 'CALL get_attendee_comments_single(' . $this->rich_start . ',' . $this->thirty_start . ')' );
      $comments_tea = $db->query( 'CALL get_attendee_comments_single(' . $this->tea_start . ',' . $this->rich_start . ')' );
      $comments_qixi = $db->query( 'CALL get_attendee_comments_single(0,' . $this->tea_start . ')' );

      return new Template( 'FFview_comment', [ 'comments' => $comments, 'comments_thirty' => $comments_thirty, 'comments_rich' => $comments_rich, 'comments_tea' => $comments_tea, 'comments_qixi' => $comments_qixi ] );
   }

   protected function _getAgeStatJSON( $startTime, $endTime )
   {
      $db = $this->db;
      $counts = $db->query( 'CALL get_age_stat_single(' . $startTime . ',' . $endTime . ')' );
      $ages = [
         '<=22' => 0,
         '23~25' => 0,
         '26~28' => 0,
         '29~31' => 0,
         '32~34' => 0,
         '>=35' => 0
      ];
      $dist = [
         0 => $ages,
         1 => $ages
      ];
      $stat = [
         0 => [ ],
         1 => [ ]
      ];
      $total = [
         0 => 0,
         1 => 0
      ];


      foreach ( $counts as $c )
      {
         $sex = (int) $c[ 'sex' ];

         $total[ $sex ] += $c[ 'count' ];

         if ( $c[ 'age' ] < 23 )
         {
            $dist[ $sex ][ '<=22' ] += $c[ 'count' ];
         }
         elseif ( $c[ 'age' ] < 26 )
         {
            $dist[ $sex ][ '23~25' ] += $c[ 'count' ];
         }
         elseif ( $c[ 'age' ] < 29 )
         {
            $dist[ $sex ][ '26~28' ] += $c[ 'count' ];
         }
         elseif ( $c[ 'age' ] < 32 )
         {
            $dist[ $sex ][ '29~31' ] += $c[ 'count' ];
         }
         elseif ( $c[ 'age' ] < 35 )
         {
            $dist[ $sex ][ '32~34' ] += $c[ 'count' ];
         }
         else
         {
            $dist[ $sex ][ '>=35' ] += $c[ 'count' ];
         }
      }

      foreach ( $dist as $sex => $counts )
      {
         foreach ( $counts as $ages => $count )
         {
            $stat[ $sex ][] = [ $ages, $count ];
         }
      }

      foreach ( $stat as $sex => $counts )
      {
         $stat[ $sex ] = [
            'total' => $total[ $sex ],
            'json' => \json_encode( $counts )
         ];
      }

      return $stat;
   }

   protected function _footer()
   {
      $c = \array_pop( $this->db->query( 'CALL get_stat_single(' . $this->thirty_two_start . ')' ) );
      $r = [
         'visitorCount' => $c[ 'visitor' ],
         'hitCount' => $c[ 'hit' ],
         'commentCount' => $c[ 'comment' ],
         'attendeeCount' => $c[ 'male' ] + $c[ 'female' ],
         'maleCount' => $c[ 'male' ],
         'femaleCount' => $c[ 'female' ],
         'subscriberCount' => $c[ 'subscriber' ],
         'attendeeCount_prev' => 193,
         'maleCount_prev' => 110,
         'femaleCount_prev' => 83
      ];
      return new Template( 'FFpage_footer', $r );
   }

}

//__END_OF_FILE__