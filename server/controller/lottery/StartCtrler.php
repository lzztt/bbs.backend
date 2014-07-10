<?php

namespace site\controller\lottery;

use site\controller\Lottery;
use lzx\html\Template;
use lzx\db\DB;
use site\dbobject\User;

class StartCtrler extends Lottery
{

   public function run()
   {
      if ( $this->request->uid == self::GUEST_UID )
      {
         $this->html->var[ 'content' ] = '<div class="messagebox">您需要拥有HoustonBBS帐号，登录后才能开始正式抽奖<br />'
            . '<a class="bigbutton" href="/user/login">登录</a><a class="bigbutton" href="/user/register">注册</a></div>';
         return;
      }

      $user = new User( $this->request->uid, 'username,firstname,birthday' );

      $birthday = \sprintf( '%08u', $user->birthday );
      $bmonth = (int) \substr( $birthday, 4, 2 );
      $bday = (int) \substr( $birthday, 6, 2 );

      if ( empty( $user->firstname ) || $bmonth * $bday == 0 )
      {
         $this->html->var[ 'content' ] = '<div class="messagebox">个人认证信息不全，您需要提供您的名字(First Name)和生日(Month & Day)后才能抽奖<br />'
            . '名字和出生月日需要与您的ID上的一致，名字必须为英文拼写，否则抽奖结果无效<br />'
            . '正式抽奖开始后将不能更改名字和月份日期信息，否则您的抽奖结果失效<br />'
            . '<a class="bigbutton" href="/user/edit#personal">填写名字和出生月份日期信息</a></div>';
      }

      // DB GET rounds time and quota

      if ( $this->request->timestamp < $t_start )
      {
         $round = 0;
         $isActive = FALSE;
         $t = $t_start - $this->request->timestamp;
         $days = \floor( $t / (3600 * 24) );
         $t = $t % (3600 * 24);
         $hours = \floor( $t / 3600 );
         $t = $t % (3600);
         $minutes = \floor( $t / 60 );
         $seconds = $t % (60);
         $this->html->var[ 'content' ] = '<div class="messagebox">正式抽奖将从<span class="highlight">2011年12月16日 00点00分00秒</span>(美国中部时间)开始<br />'
            . '距离开始还有<span class="highlight">' . $days . '</span>天<span class="highlight">' . $hours . '</span>小时<span class="highlight">' . $minutes . '</span>分<span class="highlight">' . $seconds . '</span>秒</div>';
      }
      else
      {
         $round = 0;
         $isActive = FALSE;
         $this->html->var[ 'content' ] = '<div class="messagebox">抽奖已于2012/01/18 21点结束，您现在不能进行抽奖。</div>';
      }

      $db = DB::getInstance();

      if ( $this->args[ 2 ] === 'run' )
      {
         if ( $isActive !== TRUE )
         {
            $this->html->var[ 'content' ] = '<div class="messagebox">您现在不能进行抽奖。</div>';
         }

         $lastLotteryTime = $_COOKIE[ 'lastLotteryTime' ];
         if ( $lastLotteryTime <= 0 )
         {
            //DB GET last lottery time
         }

         IF ( $this->request->timestamp - $lastLotteryTime < 60 )
         {
            $this->html->var[ 'content' ] = '<div class="messagebox">您的动作太快了，两次抽奖的间隔时间最少为60秒钟<br />请稍候点击下面抽奖按钮再试<br /><a class="bigbutton" href="/lottery/start/run">点击抽奖</a></div>';
         }

         \setcookie( 'lastLotteryTime', $this->request->timestamp, COOKIE_LIFETIME, COOKIE_PATH, '.' . DOMAIN );
         $code = \strtolower( $user->firstName ) . '_' . \substr( $birthday, 4, 4 );
         $points = \mt_rand( 0, 100 );
         // DB ADD lottery points
      }

      if ( !isset( $aPoints ) )
      {
         // DB GET average points
         $aPoints = [ ];
      }
      \krsort( $aPoints );

      $results = [ ];
      switch ($round)
      {
         // DB get detailed points
      }

      foreach ( $aPoints as $k => $v )
      {
         if ( $v < 0.0001 )
         {
            unset( $aPoints[ $k ] );
         }
      }
      foreach ( $results as $k => $v )
      {
         if ( \sizeof( $v ) == 0 )
         {
            unset( $results[ $k ] );
         }
      }

      $this->html->var[ 'content' ] = new Template( 'lotteryStart', ['average' => $average, 'aPoints' => $aPoints, 'results' => $results ] );
   }

}

//__END_OF_FILE__