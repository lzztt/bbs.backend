<?php

namespace site\controller;

use lzx\core\Controller;
use lzx\html\Template;
use site\dataobject\User;

class Lottery extends Controller
{

   public function run()
   {
      $page = $this->loadController('Page');
      $page->updateInfo();
      $page->setPage();

      $this->cache->setStatus(FALSE);

      $func = (isset($this->request->args[1]) ? $this->request->args[1] : 'rules') . 'Handler';
      $content = method_exists($this, $func) ? $this->$func() : $this->pageNotFound();

      $links = array(
         'rules' => '规则',
         'prizes' => '奖品',
         'try' => '试一下',
         'start' => '开始抽奖',
         'rank' => '排名'
      );

      $active = in_array($this->request->args[1], array_keys($links), TRUE) ? $this->request->args[1] : 'home';

      $header = '<div id="navbar">';
      foreach ($links as $k => $v)
      {
         $header .= '<a href="/lottery/' . $k . '" class="navlink' . ($k === $active ? ' active' : '') . '">' . $v . '</a>';
      }
      $header .= '</div>';


      $this->html->var['content'] = new Template('lottery', array(
            'header' => $header,
            'content' => $content
         ));

      $this->cache->storePage($output);
      //exit($output);
   }

   public function pageNotFound()
   {
      return 'Page Not Found';
   }

   public function rulesHandler()
   {
      return new Template('lotteryRules');
   }

   public function prizesHandler()
   {

      return new Template('lotteryPrizes');
   }

   public function tryHandler()
   {
      $lottery = is_array($this->session->lottery) ? $this->session->lottery : array();

      if ($this->request->args[2] === 'run')
      {
         if (isset($lottery[$this->request->timestamp]))
         {
            die('Please Slow Down<br /><a href="' . $this->request->referer . '">go back</a>');
         }
         $lottery[$this->request->timestamp] = mt_rand(0, 100);
         $this->session->lottery = $lottery;
         $this->request->redirect();
      }
      if ($this->request->args[2] === 'clear')
      {
         unset($this->session->lottery);
         $this->request->redirect();
      }

      krsort($lottery);

      return new Template('lotteryTry', array('lottery' => $lottery));
   }

   public function startHandler()
   {
      if ($this->request->uid == 0)
      {
         return '<div class="messagebox">您需要拥有HoustonBBS帐号，登录后才能开始正式抽奖<br />'
            . '<a class="bigbutton" href="/user/login">登录</a><a class="bigbutton" href="/user/register">注册</a></div>';
      }



      $user = new User($this->request->uid, 'username,firstName,birthday');

      $birthday = sprintf('%08u', $user->birthday);
      $bmonth = (int) substr($birthday, 4, 2);
      $bday = (int) substr($birthday, 6, 2);

      if (empty($user->firstName) || $bmonth * $bday == 0)
      {
         return '<div class="messagebox">个人认证信息不全，您需要提供您的名字(First Name)和生日(Month & Day)后才能抽奖<br />'
            . '名字和出生月日需要与您的ID上的一致，名字必须为英文拼写，否则抽奖结果无效<br />'
            . '正式抽奖开始后将不能更改名字和月份日期信息，否则您的抽奖结果失效<br />'
            . '<a class="bigbutton" href="/user/edit#personal">填写名字和出生月份日期信息</a></div>';
      }

      $t_start = strtotime('2011/12/16 00:00:00');
      $t_16 = strtotime('2011/12/23 20:00:00');
      $t_8 = strtotime('2011/12/30 21:00:00');
      $t_4 = strtotime('2012/01/06 21:00:00');
      $t_2 = strtotime('2012/01/12 21:00:00');
      $t_1 = strtotime('2012/01/18 21:00:00');

      if ($this->request->timestamp < $t_start)
      {
         $round = 0;
         $isActive = FALSE;
         $t = $t_start - $this->request->timestamp;
         $days = floor($t / (3600 * 24));
         $t = $t % (3600 * 24);
         $hours = floor($t / 3600);
         $t = $t % (3600);
         $minutes = floor($t / 60);
         $seconds = $t % (60);
         return '<div class="messagebox">正式抽奖将从<span class="highlight">2011年12月16日 00点00分00秒</span>(美国中部时间)开始<br />'
            . '距离开始还有<span class="highlight">' . $days . '</span>天<span class="highlight">' . $hours . '</span>小时<span class="highlight">' . $minutes . '</span>分<span class="highlight">' . $seconds . '</span>秒</div>';
      }
      elseif ($this->request->timestamp < $t_16)
      {
         $round = 1;
         $isActive = TRUE;
      }
      elseif ($this->request->timestamp < $t_8)
      {
         $round = 2;
         $uids = array(
            1428,
            1000,
            685,
            1040,
            789,
            763,
            330,
            5139,
            4694,
            3665,
            3712,
            1055,
            1894,
            3222,
            4829,
            1065,
         );
         $isActive = in_array($this->request->uid, $uids) ? TRUE : FALSE;
      }
      elseif ($this->request->timestamp < $t_4)
      {
         $round = 3;
         $uids = array(
            4829,
            330,
            3222,
            3665,
            1428,
            763,
            789,
            5139,
         );
         $isActive = in_array($this->request->uid, $uids) ? TRUE : FALSE;
      }
      elseif ($this->request->timestamp < $t_2)
      {
         $round = 4;
         $uids = array(
            789,
            763,
            4829,
            3665,
         );
         $isActive = in_array($this->request->uid, $uids) ? TRUE : FALSE;
      }
      elseif ($this->request->timestamp < $t_1)
      {
         $round = 5;
         $uids = array(
            4829,
            3665,
         );
         $isActive = in_array($this->request->uid, $uids) ? TRUE : FALSE;
      }
      else
      {
         $round = 0;
         $isActive = FALSE;
         return '<div class="messagebox">抽奖已于2012/01/18 21点结束，您现在不能进行抽奖。</div>';
      }

      $db = $this->db;

      if ($this->request->args[2] === 'run')
      {
         if ($isActive !== TRUE)
         {
            return '<div class="messagebox">您现在不能进行抽奖。</div>';
         }

         $lastLotteryTime = $_COOKIE['lastLotteryTime'];
         if ($lastLotteryTime <= 0)
         {
            $lastLotteryTime = $db->val('SELECT time FROM lotteryResults WHERE uid = ' . $user->uid . ' ORDER BY time DESC LIMIT 1');
         }

         IF ($this->request->timestamp - $lastLotteryTime < 60)
         {
            return '<div class="messagebox">您的动作太快了，两次抽奖的间隔时间最少为60秒钟<br />请稍候点击下面抽奖按钮再试<br /><a class="bigbutton" href="/lottery/start/run">点击抽奖</a></div>';
         }

         setcookie('lastLotteryTime', $this->request->timestamp, COOKIE_LIFETIME, COOKIE_PATH, '.' . DOMAIN);
         $code = strtolower($user->firstName) . '_' . substr($birthday, 4, 4);
         $points = mt_rand(0, 100);
         $db->query('INSERT INTO lotteryResults VALUES (' . $user->uid . ',' . $points . ',' . $this->request->timestamp . ',"' . $code . '")');

         $aPoints = array();
         switch ($round)
         {
            case 5:
               $aPoints[5] = $db->val('SELECT SUM(points)/COUNT(*) FROM lotteryResults WHERE uid = ' . $user->uid . ' AND time > ' . $t_2 . ' AND time < ' . $t_1);
            case 4:
               $aPoints[4] = $db->val('SELECT SUM(points)/COUNT(*) FROM lotteryResults WHERE uid = ' . $user->uid . ' AND time > ' . $t_4 . ' AND time < ' . $t_2);
            case 3:
               $aPoints[3] = $db->val('SELECT SUM(points)/COUNT(*) FROM lotteryResults WHERE uid = ' . $user->uid . ' AND time > ' . $t_8 . ' AND time < ' . $t_4);
            case 2:
               $aPoints[2] = $db->val('SELECT SUM(points)/COUNT(*) FROM lotteryResults WHERE uid = ' . $user->uid . ' AND time > ' . $t_16 . ' AND time < ' . $t_8);
            case 1:
               $aPoints[1] = $db->val('SELECT SUM(points)/COUNT(*) FROM lotteryResults WHERE uid = ' . $user->uid . ' AND time > ' . $t_start . ' AND time < ' . $t_16);
         }

         $average = 0;
         foreach ($aPoints as $i => $v)
         {
            if ($v < 0.0001)
            {
               $aPoints[$i] = 0;
            }
            $average += ($v * pow(10, ($i - 1)));
         }
         ksort($aPoints);
         for ($i = 1; $i <= sizeof($aPoints); $i++)
         {
            $keys[] = 'points' . $i;
         }
         $db->query('REPLACE lotteryUsers (uid, username, points, ' . implode(', ', $keys) . ') VALUES (' . $user->uid . ',"' . $user->username . '",' . $average . ', ' . implode(', ', $aPoints) . ')');
      }

      if (!isset($aPoints))
      {
         $points = $db->row('SELECT points, points1, points2, points3, points4, points5 FROM lotteryUsers WHERE uid = ' . $user->uid);
         $average = $points['points'];
         $aPoints = array();
         for ($i = 1; $i < sizeof($points); $i++)
         {
            $k = 'points' . $i;
            if ($points[$k] > 0.00001)
            {
               $aPoints[$i] = $points[$k];
            }
         }
      }
      krsort($aPoints);

      $results = array();
      switch ($round)
      {
         case 5:
            $results[5] = $db->select('SELECT points, time, code FROM lotteryResults WHERE uid = ' . $user->uid . ' AND time > ' . $t_2 . ' AND time < ' . $t_1 . ' ORDER BY time DESC');
         case 4:
            $results[4] = $db->select('SELECT points, time, code FROM lotteryResults WHERE uid = ' . $user->uid . ' AND time > ' . $t_4 . ' AND time < ' . $t_2 . ' ORDER BY time DESC');
         case 3:
            $results[3] = $db->select('SELECT points, time, code FROM lotteryResults WHERE uid = ' . $user->uid . ' AND time > ' . $t_8 . ' AND time < ' . $t_4 . ' ORDER BY time DESC');
         case 2:
            $results[2] = $db->select('SELECT points, time, code FROM lotteryResults WHERE uid = ' . $user->uid . ' AND time > ' . $t_16 . ' AND time < ' . $t_8 . ' ORDER BY time DESC');
         case 1:
            $results[1] = $db->select('SELECT points, time, code FROM lotteryResults WHERE uid = ' . $user->uid . ' AND time > ' . $t_start . ' AND time < ' . $t_16 . ' ORDER BY time DESC');
      }

      foreach ($aPoints as $k => $v)
      {
         if ($v < 0.0001)
         {
            unset($aPoints[$k]);
         }
      }
      foreach ($results as $k => $v)
      {
         if (sizeof($v) == 0)
         {
            unset($results[$k]);
         }
      }

      return new Template('lotteryStart', array('average' => $average, 'aPoints' => $aPoints, 'results' => $results));
   }

   public function fixHandlerDisabled()
   {
      $round = 3;
      $db = $this->db;
      $t_start = strtotime('2011/12/16 00:00:00');
      $t_16 = strtotime('2011/12/23 20:00:00');
      $t_8 = strtotime('2011/12/30 21:00:00');
      $t_4 = strtotime('2012/01/06 21:00:00');
      $t_2 = strtotime('2012/01/12 21:00:00');
      $t_1 = strtotime('2012/01/18 21:00:00');



      $users = $db->select('SELECT uid FROM lotteryUsers');
      $fixed_uids = '';
      foreach ($users as $u)
      {
         $user = new User($u['uid'], 'username');

         $aPoints = array();
         switch ($round)
         {
            case 5:
               $aPoints[5] = $db->val('SELECT SUM(points)/COUNT(*) FROM lotteryResults WHERE uid = ' . $user->uid . ' AND time > ' . $t_2 . ' AND time < ' . $t_1);
            case 4:
               $aPoints[4] = $db->val('SELECT SUM(points)/COUNT(*) FROM lotteryResults WHERE uid = ' . $user->uid . ' AND time > ' . $t_4 . ' AND time < ' . $t_2);
            case 3:
               $aPoints[3] = $db->val('SELECT SUM(points)/COUNT(*) FROM lotteryResults WHERE uid = ' . $user->uid . ' AND time > ' . $t_8 . ' AND time < ' . $t_4);
            case 2:
               $aPoints[2] = $db->val('SELECT SUM(points)/COUNT(*) FROM lotteryResults WHERE uid = ' . $user->uid . ' AND time > ' . $t_16 . ' AND time < ' . $t_8);
            case 1:
               $aPoints[1] = $db->val('SELECT SUM(points)/COUNT(*) FROM lotteryResults WHERE uid = ' . $user->uid . ' AND time > ' . $t_start . ' AND time < ' . $t_16);
         }

         $average = 0;
         foreach ($aPoints as $i => $v)
         {
            if ($v < 0.0001)
            {
               $aPoints[$i] = 0;
            }
            $average += ($v * pow(10, ($i - 1)));
         }
         ksort($aPoints);

         $keys = array();
         for ($i = 1; $i <= sizeof($aPoints); $i++)
         {
            $keys[] = 'points' . $i;
         }
         $db->query('REPLACE lotteryUsers (uid, username, points, ' . implode(', ', $keys) . ') VALUES (' . $user->uid . ',"' . $user->username . '",' . $average . ', ' . implode(', ', $aPoints) . ')');
         $fixed_uids .= $user->uid . ' : ' . $user->username . '<br />';
      }
      return $fixed_uids;
   }

   public function rankHandler()
   {
      if ($this->request->uid == 0)
      {
         return '<div class="messagebox">您需要拥有HoustonBBS帐号，登录后才能查看其他用户的排名和抽奖记录<br />'
            . '<a class="bigbutton" href="/user/login">登录</a><a class="bigbutton" href="/user/register">注册</a></div>';
      }

      $db = $this->db;

      if ($this->request->args[2] === 'record' && is_numeric($this->request->args[3]))
      {
         $t_start = strtotime('2011/12/16 00:00:00');
         $t_16 = strtotime('2011/12/23 20:00:00');
         $t_8 = strtotime('2011/12/30 21:00:00');
         $t_4 = strtotime('2012/01/06 21:00:00');
         $t_2 = strtotime('2012/01/12 21:00:00');
         $t_1 = strtotime('2012/01/18 21:00:00');

         if ($this->request->timestamp < $t_start)
         {
            $round = 0;
         }
         elseif ($this->request->timestamp < $t_16)
         {
            $round = 1;
         }
         elseif ($this->request->timestamp < $t_8)
         {
            $round = 2;
         }
         elseif ($this->request->timestamp < $t_4)
         {
            $round = 3;
         }
         elseif ($this->request->timestamp < $t_2)
         {
            $round = 4;
         }
         elseif ($this->request->timestamp < $t_1)
         {
            $round = 5;
         }
         else
         {
            $round = 5;
         }

         $uid = (int) $this->request->args[3];

         $points = $db->row('SELECT username, points, points1, points2, points3, points4, points5 FROM lotteryUsers WHERE uid = ' . $uid);
         $username = $points['username'];
         $average = $points['points'];
         $aPoints = array();
         for ($i = 1; $i <= 5; $i++)
         {
            $k = 'points' . $i;
            if ($points[$k] > 0.00001)
            {
               $aPoints[$i] = $points[$k];
            }
         }

         krsort($aPoints);

         $results = array();
         switch ($round)
         {
            case 5:
               $results[5] = $db->select('SELECT points, time, code FROM lotteryResults WHERE uid = ' . $uid . ' AND time > ' . $t_2 . ' AND time < ' . $t_1 . ' ORDER BY time DESC');
            case 4:
               $results[4] = $db->select('SELECT points, time, code FROM lotteryResults WHERE uid = ' . $uid . ' AND time > ' . $t_4 . ' AND time < ' . $t_2 . ' ORDER BY time DESC');
            case 3:
               $results[3] = $db->select('SELECT points, time, code FROM lotteryResults WHERE uid = ' . $uid . ' AND time > ' . $t_8 . ' AND time < ' . $t_4 . ' ORDER BY time DESC');
            case 2:
               $results[2] = $db->select('SELECT points, time, code FROM lotteryResults WHERE uid = ' . $uid . ' AND time > ' . $t_16 . ' AND time < ' . $t_8 . ' ORDER BY time DESC');
            case 1:
               $results[1] = $db->select('SELECT points, time, code FROM lotteryResults WHERE uid = ' . $uid . ' AND time > ' . $t_start . ' AND time < ' . $t_16 . ' ORDER BY time DESC');
         }

         foreach ($aPoints as $k => $v)
         {
            if ($v < 0.0001)
            {
               unset($aPoints[$k]);
            }
         }
         foreach ($results as $k => $v)
         {
            if (sizeof($v) == 0)
            {
               unset($results[$k]);
            }
         }

         if (sizeof($results) > 0)
         {
            return new Template('lotteryRecord', array('username' => $username, 'average' => $average, 'aPoints' => $aPoints, 'results' => $results));
         }
         else
         {
            return '<div class="messagebox">该用户无抽奖记录</div>';
         }
      }

      $rank = $db->select('SELECT uid, username, points, points1, points2, points3, points4, points5 FROM lotteryUsers ORDER BY points DESC');
      $userCount = sizeof($rank);
      $recordCount = $db->val('SELECT COUNT(*) FROM lotteryResults');
      return new Template('lotteryRank', array('userCount' => $userCount, 'recordCount' => $recordCount, 'rank' => $rank));
   }

}

//__END_OF_FILE__