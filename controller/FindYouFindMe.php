<?php

namespace site\controller;

use lzx\core\Controller;
use site\dataobject\FFAttendee;
use site\dataobject\FFComment;
use site\dataobject\FFSubscriber;
use lzx\html\Template;
use lzx\core\MySQL;
use lzx\core\Mailer;

/**
 * @property \lzx\core\MySQL $db database object
 */
class FindYouFindMe extends Controller
{

   private $tea_start = '2011-08-21';
   private $rich_start = '2012-03-11';
   private $thirty_start = '2013-03-19';
   private $db;

   public function run()
   {
      $this->cache->setStatus(FALSE);

      $this->db = MySQL::getInstance();

      $this->tea_start = \strtotime($this->tea_start);
      $this->rich_start = \strtotime($this->rich_start);
      $this->thirty_start = \strtotime($this->thirty_start);

      $func = ($this->request->args[1] ? $this->request->args[1] : 'show') . 'Action';
      if (\method_exists($this, $func))
      {
         $this->$func();
         $this->html->tpl = 'FindYouFindMe';
         $this->html->var['header'] = new Template('FFpage_header');
         $this->html->var['footer'] = $this->footerAction(TRUE);

         $this->request->pageExit($this->html);
      }
      else
      {
         $this->request->pageNotFound();
      }
   }

   public function nodeAction()
   {
      $this->request->redirect('/node/24037');
   }

   // show activity details
   public function showAction()
   {
      $vids = array("fhFadSF2vrM", "eBXpRXt-5a8");
      $vid = \mt_rand(0, 100) % sizeof($vids);
      $content = array(
         'imageSlider' => $this->getImageSlider(),
         'vid' => $vids[$vid],
      );
      $this->html->var['content'] = new Template('FFhome', $content);
      $db = $this->db;
      if ($db->val('SELECT count FROM fyfm_counts WHERE sid = ' . $db->str(session_id())) == 0)
      {
         $db->query('REPLACE fyfm_counts (sid, count) VALUES (' . $db->str(session_id()) . ', 1)');
      }
      else
      {
         $db->query('UPDATE fyfm_counts SET count = count + 1 WHERE sid = ' . $db->str(session_id()));
      }
   }

   public function getImageSlider()
   {
      $images = array(
         0 => array(
            'path' => '/data/fyfm/13118198981266.png',
            'name' => '薄荷'
         ),
         1 => array(
            'path' => '/data/fyfm/13118268141266.png',
            'name' => '七夕'
         ),
         2 => array(
            'path' => '/data/fyfm/13118224061268.png',
            'name' => '吉娃莲'
         ),
         3 => array(
            'path' => '/data/fyfm/13119630681266.png',
            'name' => '凡凡觅友'
         ),
      );

      \shuffle($images);

      $content['images'] = $images;
      return new Template('image_slider', $content);
   }

   // attend activity
   public function attendAction()
   {
      if (\file_exists($this->path['file'] . '/ffmy.msg'))
      {
         echo '<span style="color:#B22222">错误</span>: ' . \file_get_contents($this->path['file'] . '/ffmy.msg');
         exit;
      }

      if (empty($this->request->post['name']) || \strlen($this->request->post['sex']) < 1 || empty($this->request->post['age']) || empty($this->request->post['email']))
      {
         echo '<span style="color:#B22222">错误</span>: 带星号(<span class="form_required" title="此项必填。">*</span>)选项为必填选项';
         exit;
      }


      $attendee = new FFAttendee();

      $attendee->name = $this->request->post['name'];
      $attendee->sex = $this->request->post['sex'];
      $attendee->age = $this->request->post['age'];
      $attendee->email = $this->request->post['email'];
      $attendee->phone = $this->request->post['phone'];

      if ($this->request->post['comment'])
      {

         $comment = new FFComment();
         $comment->name = $this->request->post['anonymous'] ? $this->request->ip : $this->request->post['name'];
         $comment->body = $this->request->post['comment'];
         $comment->time = $this->request->timestamp;
         $comment->save();
         $attendee->cid = $comment->cid;
      }

      $attendee->time = $this->request->timestamp;
      $attendee->save();


      $mailer = new Mailer();

      $mailer->to = $attendee->email;
      $mailer->subject = $attendee->name . '，您的单身活动报名已经收到';

      $contents = array(
         'name' => $attendee->name,
         'male' => $this->db->val('SELECT count(*) FROM fyfm_attendees WHERE sex = 1 AND time > ' . $this->thirty_start),
         'female' => $this->db->val('SELECT count(*) FROM fyfm_attendees WHERE sex = 0 AND time > ' . $this->thirty_start)
      );


      $mailer->body = new Template('mail/attendee', $contents);

      echo '<script type="text/javascript">$("#footer").load("/single/footer");</script>';

//       echo '报名成功，但因为8月6号的七夕活动已经结束，确认邮件并未发送。您将在下次活动计划出来时收到带有活动详情的电子邮件确认';

      if ($mailer->send())
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
   public function commentAction()
   {
      $func = ($this->request->args[2] ? $this->request->args[2] : 'add') . 'Comment';
      $output = \method_exists($this, $func) ? $this->$func() : $this->request->pageNotFound();

      echo $output;
      exit;
   }

   public function addComment()
   {
      if (strlen($this->request->post['comment']) < 5)
      {
         return '<span style="color:#B22222">错误</span>: 留言字数最少为5个字符';
      }


      $comment = new FFComment();

      if ($this->request->post['anonymous'] || empty($this->request->post['name']))
      {
         $comment->name = $this->request->ip;
      }
      else
      {
         $comment->name = $this->request->post['name'];
      }

      $comment->body = $this->request->post['comment'];
      $comment->time = $this->request->timestamp;
      $comment->save();

      $output = '谢谢您的留言，请点击这里<a class="commentViewButton" style="color:#A0522D" href="#">查看所有留言</a>'
         . '<script type="text/javascript">$("#footer").load("/single/footer");</script>';
      return $output;
   }

   public function viewComment()
   {
      $db = $this->db;
      $comments = $db->select('SELECT name, body, time FROM fyfm_comments WHERE time > ' . $this->thirty_start . ' ORDER BY cid ASC');
      $comments_rich = $db->select('SELECT name, body, time FROM fyfm_comments WHERE time > ' . $this->rich_start . ' AND time < ' . $this->thirty_start . ' ORDER BY cid ASC');
      $comments_tea = $db->select('SELECT name, body, time FROM fyfm_comments WHERE time > ' . $this->tea_start . ' AND time < ' . $this->rich_start . ' ORDER BY cid ASC');
      $comments_qixi = $db->select('SELECT name, body, time FROM fyfm_comments WHERE time < ' . $this->tea_start . ' ORDER BY cid ASC');

      return new Template('FFview_comment', array('comments' => $comments, 'comments_rich' => $comments_rich, 'comments_tea' => $comments_tea, 'comments_qixi' => $comments_qixi));
   }

   // private attendee info
   public function attendeeAction()
   {
      if (sizeof($this->request->args) < 3 )
      {
         $this->request->pageNotFound();
      }

      if ($this->request->timestamp < strtotime("04/08/2013 22:00:00 CDT"))
      {
         $db = $this->db;
         $content = array(
            'attendees' => $db->select('SELECT a.name, a.sex, a.email, a.time, c.body FROM fyfm_attendees as a left join fyfm_comments as c on a.cid = c.cid WHERE a.time > ' . $this->thirty_start . ' order by a.aid'),
            //'ageGroup' => $db->select('SELECT sex, age, count(*) AS count FROM fyfm_attendees WHERE time > ' . $this->thirty_start . ' GROUP by sex, age order by sex, age')
         );

         $this->html->var['content'] = new Template('FFattendee', $content);
      }
      else
      {
         $this->html->var['content'] = "<p>ERROR: The page you request is not available anymore</p>";
      }
   }

   public function subscribeAction()
   {
      if (empty($this->request->post['email']))
      {
         echo '<span style="color:#B22222">错误</span>: E-Mail地址不能为空';
         exit;
      }


      $subscriber = new FFSubscriber();

      $subscriber->email = $this->request->post['email'];
      $subscriber->time = $this->request->timestamp;
      $subscriber->save();


      $mailer = new Mailer();

      $mailer->to = $subscriber->email;
      $mailer->subject = '您的单身活动关注已经收到';

      $mailer->body = "rt~\n感谢您关注单身活动";
      echo '<script type="text/javascript">$("#footer").load("/single/footer");</script>';

      if ($mailer->send())
      {
         echo '关注成功，确认邮件已发送';
      }
      else
      {
         echo '<span style="color:#B22222">错误</span>: 关注确认邮件邮寄失败';
      }

      exit;
   }

   private function getAgeStatJSON($startTime, $endTime)
   {
      $db = $this->db;
      $counts = $db->select('SELECT sex, age, count(aid) AS count FROM `fyfm_attendees` WHERE time >= ' . $startTime . ' AND time <= ' . $endTime . ' GROUP BY sex, age');
      $ages = array(
         '<=22' => 0,
         '23~25' => 0,
         '26~28' => 0,
         '29~31' => 0,
         '32~34' => 0,
         '>=35' => 0
      );
      $dist = array(
         0 => $ages,
         1 => $ages
      );
      $stat = array(
         0 => array(),
         1 => array()
      );
      $total = array(
         0 => 0,
         1 => 0
      );


      foreach ($counts as $c)
      {
         $sex = (int) $c['sex'];

         $total[$sex] += $c['count'];

         if ($c['age'] < 23)
         {
            $dist[$sex]['<=22'] += $c['count'];
         }
         elseif ($c['age'] < 26)
         {
            $dist[$sex]['23~25'] += $c['count'];
         }
         elseif ($c['age'] < 29)
         {
            $dist[$sex]['26~28'] += $c['count'];
         }
         elseif ($c['age'] < 32)
         {
            $dist[$sex]['29~31'] += $c['count'];
         }
         elseif ($c['age'] < 35)
         {
            $dist[$sex]['32~34'] += $c['count'];
         }
         else
         {
            $dist[$sex]['>=35'] += $c['count'];
         }
      }

      foreach ($dist as $sex => $counts)
      {
         foreach ($counts as $ages => $count)
         {
            $stat[$sex][] = array($ages, $count);
         }
      }

      foreach ($stat as $sex => $counts)
      {
         $stat[$sex] = array(
            'total' => $total[$sex],
            'json' => \json_encode($counts)
         );
      }

      return $stat;
   }

   public function chartAction()
   {
      $stat = array(
         '七夕' => $this->getAgeStatJSON(1312350024, 1313607290),
         '得闲饮茶' => $this->getAgeStatJSON(1313941540, 1315549125),
         '有钱人' => $this->getAgeStatJSON(1331521805, 1332011793),
      );

      $stat = array();

      $sanshi = $this->getAgeStatJSON(1363746005, 1463746005);
      $stat[] = array(
         array(
            'title' => '三十看从前聚会 女生 (' . $sanshi[0]['total'] . ')人',
            'data' => $sanshi[0]['json'],
            'div_id' => 'div_sanshi_female'
         ),
         array(
            'title' => '三十看从前聚会 男生 (' . $sanshi[1]['total'] . ')人',
            'data' => $sanshi[1]['json'],
            'div_id' => 'div_sanshi_male'
         ),
      );

      $youqianren = $this->getAgeStatJSON(1331521805, 1332011793);
      $stat[] = array(
         array(
            'title' => '有钱人聚会 女生 (' . $youqianren[0]['total'] . ')人',
            'data' => $youqianren[0]['json'],
            'div_id' => 'div_youqianren_female'
         ),
         array(
            'title' => '有钱人聚会 男生 (' . $youqianren[1]['total'] . ')人',
            'data' => $youqianren[1]['json'],
            'div_id' => 'div_youqianren_male'
         ),
      );

      $yincha = $this->getAgeStatJSON(1313941540, 1315549125);
      $stat[] = array(
         array(
            'title' => '得闲饮茶聚会 女生 (' . $yincha[0]['total'] . ')人',
            'data' => $yincha[0]['json'],
            'div_id' => 'div_yincha_female'
         ),
         array(
            'title' => '得闲饮茶聚会 男生 (' . $yincha[1]['total'] . ')人',
            'data' => $yincha[1]['json'],
            'div_id' => 'div_yincha_male'
         ),
      );

      $qixi = $this->getAgeStatJSON(1312350024, 1313607290);
      $stat[] = array(
         array(
            'title' => '七夕聚会 女生 (' . $qixi[0]['total'] . ')人',
            'data' => $qixi[0]['json'],
            'div_id' => 'div_qixi_female'
         ),
         array(
            'title' => '七夕聚会 男生 (' . $qixi[1]['total'] . ')人',
            'data' => $qixi[1]['json'],
            'div_id' => 'div_qixi_male'
         ),
      );

      echo new Template('FFchart', array('stat' => $stat));
      exit;
   }

   public function footerAction($return = FALSE)
   {
      $sql = 'SELECT'
         . ' (SELECT count(*) FROM fyfm_counts) AS visitorCount,'
         . ' (SELECT sum(count) FROM fyfm_counts) AS hitCount,'
         . ' (SELECT count(*) FROM fyfm_comments) AS commentCount,'
         . ' (SELECT count(*) FROM fyfm_attendees WHERE time > 1363912925) as attendeeCount,'
         . ' (SELECT count(*) FROM fyfm_attendees WHERE sex = 1 and time > 1363912925) as maleCount,'
         . ' (SELECT count(*) FROM fyfm_attendees WHERE sex = 0 and time > 1363912925) as femaleCount,'
         . ' (SELECT count(*) FROM fyfm_subscribers) as subscriberCount';
      $r = $this->db->row($sql);
      $r['attendeeCount_prev'] = 148;
      $r['maleCount_prev'] = 83;
      $r['femaleCount_prev'] = 65;
      $footer = new Template('FFpage_footer', $r);

      if ($return)
      {
         return $footer;
      }
      else
      {
         echo $footer;
         exit;
      }
   }

}

//__END_OF_FILE__