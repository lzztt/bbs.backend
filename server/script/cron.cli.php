<?php

namespace site;

use lzx\App;
use lzx\core\MySQL;
use lzx\core\Mailer;
use lzx\html\Template;
use lzx\core\Cache;
use site\dataobject\User;

// note: cache path in php and nginx are using server_name
$_SERVER['SERVER_NAME'] = 'www.houstonbbs.com';
// $config->domain need http_host
$_SERVER['HTTP_HOST'] = 'www.houstonbbs.com';

$_SERVERDIR = \dirname( __DIR__ );

require_once \dirname( $_SERVERDIR ) . '/lib/lzx/App.php';

class CronApp extends App
{

   protected $timestamp;

   public function run( $argc, Array $argv )
   {
      $this->timestamp = \intval( $_SERVER['REQUEST_TIME'] );

      $this->logger->setUserInfo( 'uid=cron umode=cli' );

      $task = $argv[1];
      $func = 'do_' . $task;

      if ( \method_exists( $this, $func ) )
      {
         $this->$func();
      }
      else
      {
         $this->logger->info( 'CRON JOB: wrong action : ' . $task );
         exit;
      }
   }

   protected function do_user()
   {
      $db = MySQL::getInstance( $this->config->database, TRUE );

      $user = new User();
      $user->where( 'status', NULL, '=' );
      $user->where( 'password', NULL, '=' );
      $users = $user->getList( 'uid,username,email' );

      if ( \sizeof( $users ) > 0 )
      {
         $mailer = new Mailer( $this->config->domain );
         Template::$path = $this->path['theme'] . '/' . $this->config->theme;
         foreach ( $users as $u )
         {
            $password = $user->randomPW(); // will send generated password to email

            $mailer->to = $u['email'];
            $mailer->subject = $u['username'] . ' 的帐户详情（已批准）';
            $contents = array(
               'username' => $u['username'],
               'password' => $password,
               'sitename' => 'HoustonBBS',
               'lang' => $this->config->lang_default // should get from the user record in DB
            );
            $mailer->body = new Template( 'mail/activation', $contents );

            if ( $mailer->send() === FALSE )
            {
               $this->logger->info( 'sending new user activation email error: ' . $u['email'] );
               continue;
            }

            $db->query( 'UPDATE users SET password = ' . $db->str( $user->hashPW( $password ) ) . ', status = 1 WHERE uid = ' . $u['uid'] );
            $this->logger->info( 'activate user: ' . $u['uid'] . ' : ' . $password );
            $newUsers[] = '[ID] ' . $u['uid'] . ' [USERNAME] ' . $u['username'] . ' [EMAIL] ' . $u['email'];
         }

         $mailer->to = 'admin@houstonbbs.com';
         $mailer->subject = '[用户] ' . \sizeof( $newUsers ) . '个新帐号已被系统自动激活';
         $mailer->body = \implode( \PHP_EOL, $newUsers );
         if ( $mailer->send() === FALSE )
         {
            $this->logger->info( 'sending new user activation email error.' );
         }
      }
   }

   protected function do_activity()
   {
      $cacheKey = 'recentActivities';
      $cache = Cache::getInstance( $this->config->cache_path );
      $cache->setLogger( $this->logger );
      $refreshTimeFile = $this->path['log'] . '/activity_cache_refresh_time.txt';

      $db = MySQL::getInstance( $this->config->database, TRUE );

      $activities = $db->select( 'SELECT a.startTime, n.nid, n.title, u.username, u.email FROM activities AS a JOIN nodes AS n ON a.nid = n.nid JOIN users AS u ON n.uid = u.uid WHERE a.status IS NULL' );
      if ( \sizeof( $activities ) > 0 )
      {
         $mailer = new Mailer( $this->config->domain );
         Template::$path = $this->path['theme'] . '/' . $this->config->theme;

         foreach ( $activities as $a )
         {
            $mailer->to = $a['email'];
            $mailer->subject = $a['username'] . ' 的活动详情（已激活）';
            $contents = array(
               'nid' => $a['nid'],
               'title' => $a['title'],
               'username' => $a['username'],
               'sitename' => 'HoustonBBS'
            );
            $mailer->body = new Template( 'mail/activity', $contents );

            if ( $mailer->send() === FALSE )
            {
               $this->logger->info( 'sending new activity activation email error.' );
               continue;
            }

            $db->query( 'UPDATE activities SET status = 1 WHERE nid = ' . $a['nid'] );

            $newActivities[] = '[TITLE] ' . $a['title'] . \PHP_EOL . ' [URL] http://www.houstonbbs.com/node/' . $a['nid'];
         }

         // delete cache and reschedule next refresh time
         $cache->delete( $cacheKey );
         $refreshTime = \is_readable( $refreshTimeFile ) ? \intval( \file_get_contents( $refreshTimeFile ) ) : 0;
         $currentTime = \intval( $_SERVER['REQUEST_TIME'] );
         $newActivityStartTime = $activities[0]['startTime'];
         if ( $refreshTime < $currentTime || $refreshTime > $newActivityStartTime )
         {
            $this->updateActivityCacheRefreshTime( $refreshTimeFile, $db, $refreshTime, $currentTime );
         }

         $mailer->to = 'admin@houstonbbs.com';
         $mailer->subject = '[活动] ' . \sizeof( $activities ) . '个新活动已被系统自动激活';
         $mailer->body = \implode( "\n\n", $newActivities );
         $mailer->send();
      }
      // refresh cache based on the next refresh timestamp
      else
      {
         $refreshTime = \is_readable( $refreshTimeFile ) ? \intval( \file_get_contents( $refreshTimeFile ) ) : 0;
         $currentTime = \intval( $_SERVER['REQUEST_TIME'] );
         if ( $currentTime > $refreshTime )
         {
            $cache->delete( $cacheKey );
            $this->updateActivityCacheRefreshTime( $refreshTimeFile, $db, $refreshTime, $currentTime );
         }
      }
   }

   protected function updateActivityCacheRefreshTime( $refreshTimeFile, $db, $refreshTime, $currentTime )
   {
      $nextRefreshTime = $currentTime + 604800;
      $sql = 'SELECT startTime, endTime FROM activities WHERE status = 1 AND (startTime > ' . $currentTime . ' OR endTime > ' . $currentTime . ')';
      foreach ( $db->select( $sql ) as $r )
      {
         if ( $r['startTime'] < $currentTime )
         {
            // current activity
            if ( $r['endTime'] < $nextRefreshTime )
            {
               $nextRefreshTime = $r['endTime'];
            }
         }
         else
         {
            // future activity
            if ( $r['startTime'] < $nextRefreshTime )
            {
               $nextRefreshTime = $r['startTime'];
            }
         }
      }
      \file_put_contents( $refreshTimeFile, $nextRefreshTime );
   }

// daily at 23:55 CDT
   protected function do_session()
   {
      $db = MySQL::getInstance( $this->config->database, TRUE );
      $currentTime = \intval( $_SERVER['REQUEST_TIME'] );
      $db->query( 'DELETE FROM sessions WHERE uid = 0 AND mtime < ' . ($currentTime - 21600) );
      $db->query( 'DELETE FROM sessions WHERE mtime < ' . ($currentTime - $this->config->cookie->lifetime) );
   }

// daily
   protected function do_backup()
   {
      // clean database before backup
      $db = MySQL::getInstance( $this->config->database, TRUE );
      $db->query( 'CALL clean()' );
      $db->free();
      unset( $db );

      $db = $this->config->database;
      $mysqldump = '/usr/bin/mysqldump';
      $gzip = '/bin/gzip';
      $cmd = $mysqldump . ' --opt --routines --default-character-set=utf8 --set-charset'
            . ' --user=' . $db['username'] . ' --password=' . $db['passwd'] . ' ' . $db['dbname']
            . ' | ' . $gzip . ' > ' . $this->path['backup'] . '/' . \date( 'Y-m-d', \intval( $_SERVER['REQUEST_TIME'] ) - 86400 ) . '.sql.gz';
      echo \shell_exec( $cmd );
   }

   protected function do_ad()
   {
      $db = MySQL::getInstance( $this->config->database, TRUE );

      $ads = $db->select( 'SELECT * FROM ads WHERE exp_time < ' . ($this->timestamp + 604800) . ' AND exp_time > ' . ($this->timestamp - 172800) );
      $count = \sizeof( $ads );
      if ( $count > 0 )
      {
         $mailer = new Mailer( $this->config->domain );
         Template::$path = $this->path['theme'] . '/' . $this->config->theme;

         $mailer->to = $this->config->webmaster;
         $mailer->subject = '[ ' . $count . ' ] 七天内过期广告';
         $contents = array( 'ads' => $ads );
         $mailer->body = new Template( 'mail/ads', $contents );

         if ( $mailer->send() === FALSE )
         {
            $this->logger->info( 'sending expiring ads email error.' );
            continue;
         }
      }
   }

// daily at 23:55 CDT
   protected function do_alexa()
   {
      $c = \curl_init( 'http://data.alexa.com/data?cli=10&dat=s&url=http://www.houstonbbs.com' );
      \curl_setopt_array( $c, array(
         CURLOPT_RETURNTRANSFER => TRUE,
         CURLOPT_CONNECTTIMEOUT => 2,
         CURLOPT_TIMEOUT => 3
      ) );
      $contents = \curl_exec( $c );
      \curl_close( $c );

      if ( $contents )
      {
         \preg_match( '#<POPULARITY URL="(.*?)" TEXT="([0-9]+){1,}"#si', $contents, $p );
         if ( $p[2] )
         {
            $rank = \number_format( intval( $p[2] ) );
            $data = 'HoustonBBS最近三个月平均访问量<a href="http://www.alexa.com/data/details/main?url=http://www.houstonbbs.com" title="HoustonBBS近三个月的访问量统计">Alexa排名</a>:<br /><a href="/node/5641" title="Houston各中文网站 月访问量 横向比较">第 <b>' . $rank . '</b> 位</a> (更新时间: ' . \date( 'm/d/Y H:i:s T', \intval( $_SERVER['REQUEST_TIME'] ) ) . ')';
            \file_put_contents( $this->path['theme'] . '/' . $this->config->theme . '/alexa.tpl.php', $data );
         }
         else
         {
            $this->logger->info( 'Get Alexa Rank Error' );
         }
      }
   }

}

$app = new CronApp( $_SERVERDIR . '/config.php', array( __NAMESPACE__ => $_SERVERDIR ) );

$app->run( $argc, $argv );

//_END_OF_FILE
