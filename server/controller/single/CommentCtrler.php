<?php

namespace site\controller\single;

use site\controller\Single;
use site\dbobject\FFAttendee;
use site\dbobject\FFComment;
use site\dbobject\FFSubscriber;
use lzx\html\Template;
use lzx\db\DB;
use lzx\core\Mailer;

/**
 * @property \lzx\db\DB $db database object
 */
class CommentCtrler extends Single
{

   private $tea_start = '2011-08-21';
   private $rich_start = '2012-03-11';
   private $thirty_start = '2013-03-19';
   private $thirty_two_start = '2013-08-16';
   private $db;

   /**
    * default protected methods
    */
   protected function init()
   {
      $this->cache->setStatus( FALSE );

      $this->db = DB::getInstance();

      $this->tea_start = \strtotime( $this->tea_start );
      $this->rich_start = \strtotime( $this->rich_start );
      $this->thirty_start = \strtotime( $this->thirty_start );
      $this->thirty_two_start = \strtotime( $this->thirty_two_start );
   }

   protected function _final()
   {
      $this->html->tpl = 'FindYouFindMe';

      $this->html->var[ 'header' ] = new Template( 'FFpage_header' );
      $this->html->var[ 'footer' ] = $this->_footer();
   }

   public function run()
   {
      $this->show();
   }

   /**
    * public methods
    */
   public function node()
   {
      $this->request->redirect( '/node/32576' );
   }

   // show activity details
   public function show()
   {
      $vids = [ "fhFadSF2vrM", "eBXpRXt-5a8" ];
      $vid = \mt_rand( 0, 100 ) % sizeof( $vids );
      $content = [
         'imageSlider' => $this->_getImageSlider(),
         'vid' => $vids[ $vid ],
      ];
      $this->html->var[ 'content' ] = new Template( 'FFhome', $content );

      $this->db->query( 'CALL update_view_count_single("' . \session_id() . '")' );
   }

   // attend activity
   public function attend()
   {
      if ( \file_exists( $this->config->path[ 'file' ] . '/ffmy.msg' ) )
      {
         echo '<span style="color:#B22222">错误</span>: ' . \file_get_contents( $this->config->path[ 'file' ] . '/ffmy.msg' );
         exit;
      }

      if ( empty( $this->request->post[ 'name' ] ) || \strlen( $this->request->post[ 'sex' ] ) < 1 || empty( $this->request->post[ 'age' ] ) || empty( $this->request->post[ 'email' ] ) )
      {
         echo '<span style="color:#B22222">错误</span>: 带星号(<span class="form_required" title="此项必填。">*</span>)选项为必填选项';
         exit;
      }


      $attendee = new FFAttendee();

      $attendee->name = $this->request->post[ 'name' ];
      $attendee->sex = $this->request->post[ 'sex' ];
      $attendee->age = $this->request->post[ 'age' ];
      $attendee->email = $this->request->post[ 'email' ];
      $attendee->phone = $this->request->post[ 'phone' ];

      if ( $this->request->post[ 'comment' ] )
      {

         $comment = new FFComment();
         $comment->name = $this->request->post[ 'anonymous' ] ? $this->request->ip : $this->request->post[ 'name' ];
         $comment->body = $this->request->post[ 'comment' ];
         $comment->time = $this->request->timestamp;
         $comment->add();
         $attendee->cid = $comment->id;
      }

      $attendee->time = $this->request->timestamp;
      $attendee->add();


      $mailer = new Mailer();

      $mailer->to = $attendee->email;
      $mailer->subject = $attendee->name . '，您的单身活动报名已经收到';

      $count = \array_pop( $this->db->query( 'CALL get_attendee_count_single(' . $this->thirty_two_start . ')' ) );
      $contents = [
         'name' => $attendee->name,
         'male' => $count[ 'male' ],
         'female' => $count[ 'female' ]
      ];

      $mailer->body = new Template( 'mail/attendee', $contents );

      echo '<script type="text/javascript">$("#footer").load("/single/footer");</script>';

//       echo '报名成功，但因为8月6号的七夕活动已经结束，确认邮件并未发送。您将在下次活动计划出来时收到带有活动详情的电子邮件确认';

      if ( $mailer->send() )
      {
         echo '报名成功，确认邮件已发送';
      }
      else
      {
         echo '<span style="color:#B22222">错误</span>: 报名确认邮件邮寄失败';
      }

      exit;
   }

   // public comments
   public function comment()
   {
      echo $this->request->post ? $this->_addComment() : $this->_viewComment();
      exit;
   }

   // private attendee info
   public function attendee()
   {
      if ( TRUE )//$this->request->timestamp < strtotime( "09/16/2013 22:00:00 CDT" ) )
      {
         $db = $this->db;
         $content = [
            'attendees' => $db->query( 'CALL get_attendees_single(' . $this->thirty_two_start . ')' )
         ];

         $this->html->var[ 'content' ] = new Template( 'FFattendee', $content );
      }
      else
      {
         $this->html->var[ 'content' ] = "<p>ERROR: The page you request is not available anymore</p>";
      }
   }

   public function subscribe()
   {
      if ( empty( $this->request->post[ 'email' ] ) )
      {
         echo '<span style="color:#B22222">错误</span>: E-Mail地址不能为空';
         exit;
      }


      $subscriber = new FFSubscriber();

      $subscriber->email = $this->request->post[ 'email' ];
      $subscriber->time = $this->request->timestamp;
      $subscriber->add();


      $mailer = new Mailer();

      $mailer->to = $subscriber->email;
      $mailer->subject = '您的单身活动关注已经收到';

      $mailer->body = "rt~\n感谢您关注单身活动";
      echo '<script type="text/javascript">$("#footer").load("/single/footer");</script>';

      if ( $mailer->send() )
      {
         echo '关注成功，确认邮件已发送';
      }
      else
      {
         echo '<span style="color:#B22222">错误</span>: 关注确认邮件邮寄失败';
      }

      exit;
   }

   public function chart()
   {
      $stat = [
         '七夕' => $this->_getAgeStatJSON( 1312350024, 1313607290 ),
         '得闲饮茶' => $this->_getAgeStatJSON( 1313941540, 1315549125 ),
         '有钱人' => $this->_getAgeStatJSON( 1331521805, 1332011793 ),
         '三十看从前' => $this->_getAgeStatJSON( 1363746005, 1376629987 ),
      ];

      $stat = [ ];

      $sanshi_two = $this->_getAgeStatJSON( 1376629987, 1463746005 );
      $stat[] = [
         [
            'title' => '三十看从前聚会(二) 女生 (' . $sanshi_two[ 0 ][ 'total' ] . ')人',
            'data' => $sanshi_two[ 0 ][ 'json' ],
            'div_id' => 'div_sanshi_two_female'
         ],
         [
            'title' => '三十看从前聚会(二) 男生 (' . $sanshi_two[ 1 ][ 'total' ] . ')人',
            'data' => $sanshi_two[ 1 ][ 'json' ],
            'div_id' => 'div_sanshi_two_male'
         ],
      ];

      $sanshi = $this->_getAgeStatJSON( 1363746005, 1376629987 );
      $stat[] = [
         [
            'title' => '三十看从前聚会 女生 (' . $sanshi[ 0 ][ 'total' ] . ')人',
            'data' => $sanshi[ 0 ][ 'json' ],
            'div_id' => 'div_sanshi_female'
         ],
         [
            'title' => '三十看从前聚会 男生 (' . $sanshi[ 1 ][ 'total' ] . ')人',
            'data' => $sanshi[ 1 ][ 'json' ],
            'div_id' => 'div_sanshi_male'
         ],
      ];

      $youqianren = $this->_getAgeStatJSON( 1331521805, 1332011793 );
      $stat[] = [
         [
            'title' => '有钱人聚会 女生 (' . $youqianren[ 0 ][ 'total' ] . ')人',
            'data' => $youqianren[ 0 ][ 'json' ],
            'div_id' => 'div_youqianren_female'
         ],
         [
            'title' => '有钱人聚会 男生 (' . $youqianren[ 1 ][ 'total' ] . ')人',
            'data' => $youqianren[ 1 ][ 'json' ],
            'div_id' => 'div_youqianren_male'
         ],
      ];

      $yincha = $this->_getAgeStatJSON( 1313941540, 1315549125 );
      $stat[] = [
         [
            'title' => '得闲饮茶聚会 女生 (' . $yincha[ 0 ][ 'total' ] . ')人',
            'data' => $yincha[ 0 ][ 'json' ],
            'div_id' => 'div_yincha_female'
         ],
         [
            'title' => '得闲饮茶聚会 男生 (' . $yincha[ 1 ][ 'total' ] . ')人',
            'data' => $yincha[ 1 ][ 'json' ],
            'div_id' => 'div_yincha_male'
         ],
      ];

      $qixi = $this->_getAgeStatJSON( 1312350024, 1313607290 );
      $stat[] = [
         [
            'title' => '七夕聚会 女生 (' . $qixi[ 0 ][ 'total' ] . ')人',
            'data' => $qixi[ 0 ][ 'json' ],
            'div_id' => 'div_qixi_female'
         ],
         [
            'title' => '七夕聚会 男生 (' . $qixi[ 1 ][ 'total' ] . ')人',
            'data' => $qixi[ 1 ][ 'json' ],
            'div_id' => 'div_qixi_male'
         ],
      ];

      echo new Template( 'FFchart', [ 'stat' => $stat ] );
      exit;
   }

   public function footer()
   {
      echo $this->_footer();
      exit;
   }

   /**
    * 
    * private methods
    */
   private function _getImageSlider()
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

   private function _addComment()
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

   private function _viewComment()
   {
      $db = $this->db;
      $comments = $db->query( 'CALL get_attendee_comments_single(' . $this->thirty_two_start . ',' . $this->request->timestamp . ')' );
      $comments_thirty = $db->query( 'CALL get_attendee_comments_single(' . $this->thirty_start . ',' . $this->thirty_two_start . ')' );
      $comments_rich = $db->query( 'CALL get_attendee_comments_single(' . $this->rich_start . ',' . $this->thirty_start . ')' );
      $comments_tea = $db->query( 'CALL get_attendee_comments_single(' . $this->tea_start . ',' . $this->rich_start . ')' );
      $comments_qixi = $db->query( 'CALL get_attendee_comments_single(0,' . $this->tea_start . ')' );

      return new Template( 'FFview_comment', [ 'comments' => $comments, 'comments_thirty' => $comments_thirty, 'comments_rich' => $comments_rich, 'comments_tea' => $comments_tea, 'comments_qixi' => $comments_qixi ] );
   }

   private function _getAgeStatJSON( $startTime, $endTime )
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

   private function _footer()
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