<?php

namespace site;

use lzx\App;
use lzx\db\DB;
use lzx\core\Mailer;
use lzx\html\Template;
use lzx\cache\Cache;
use lzx\cache\CacheHandler;
use site\Config;

// note: cache path in php and nginx are using server_name
$_SERVER[ 'SERVER_NAME' ] = 'www.houstonbbs.com';
// $config->domain need http_host
$_SERVER[ 'HTTP_HOST' ] = 'www.houstonbbs.com';

require_once \dirname( __DIR__ ) . '/lib/lzx/App.php';

class CronApp extends App
{

   protected $timestamp;
   protected $config;

   public function __construct()
   {
      parent::__construct();
      // register current namespaces
      $this->loader->registerNamespace( __NAMESPACE__, \dirname( __DIR__ ) . '/server' );

      $this->timestamp = \intval( $_SERVER[ 'REQUEST_TIME' ] );
      $this->config = Config::getInstance();
      $this->logger->setUserInfo( [ 'uid' => 'cron', 'umode' => 'cli', 'urole' => 'adm' ] );
      $this->logger->setDir( $this->config->path[ 'log' ] );
      $this->logger->setEmail( $this->config->webmaster );
   }

   public function run( $argc, Array $argv = [ ] )
   {
      $task = $argv[ 1 ];
      $func = 'do_' . $task;

      // for logger mail subject
      $_SERVER[ 'REQUEST_URI' ] = 'cron->' . $task;

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

   protected function do_activity()
   {
      // config cache
      $db = DB::getInstance( $this->config->db );
      $site = 'houston';

      CacheHandler::$path = $this->config->path[ 'cache' ];
      $cacheHandler = CacheHandler::getInstance( $db );
      $cacheHandler->setCacheTreeTable( $cacheHandler->getCacheTreeTable() . '_' . $site );
      $cacheHandler->setCacheEventTable( $cacheHandler->getCacheEventTable() . '_' . $site );
      Cache::setHandler( $cacheHandler );
      Cache::setLogger( $this->logger );

      $cache = $cacheHandler->createCache( 'recentActivities' );
      $refreshTimeFile = $this->config->path[ 'log' ] . '/activity_cache_refresh_time.txt';

      $activities = $db->query( 'SELECT a.start_time, n.id, n.title, u.username, u.email FROM activities AS a JOIN nodes AS n ON a.nid = n.id JOIN users AS u ON n.uid = u.id WHERE a.status IS NULL' );
      if ( \sizeof( $activities ) > 0 )
      {
         $mailer = new Mailer();
         Template::$path = $this->config->path[ 'theme' ] . '/' . $this->config->theme[ 'roselife' ];

         foreach ( $activities as $a )
         {
            $mailer->to = $a[ 'email' ];
            $mailer->subject = $a[ 'username' ] . ' 的活动详情（已激活）';
            $contents = [
               'nid' => $a[ 'id' ],
               'title' => $a[ 'title' ],
               'username' => $a[ 'username' ],
               'domain' => 'www.houstonbbs.com',
               'sitename' => 'HoustonBBS'
            ];
            $mailer->body = new Template( 'mail/activity', $contents );

            if ( $mailer->send() === FALSE )
            {
               $this->logger->info( 'sending new activity activation email error.' );
               continue;
            }

            $db->query( 'UPDATE activities SET status = 1 WHERE nid = ' . $a[ 'id' ] );

            $newActivities[] = '[TITLE] ' . $a[ 'title' ] . \PHP_EOL . ' [URL] http://www.houstonbbs.com/node/' . $a[ 'id' ];
         }

         // delete cache and reschedule next refresh time
         $cache->delete();
         $cache->flush();
         $refreshTime = \is_readable( $refreshTimeFile ) ? (int) \file_get_contents( $refreshTimeFile ) : 0;
         $currentTime = (int) $_SERVER[ 'REQUEST_TIME' ];
         $newActivityStartTime = $activities[ 0 ][ 'start_time' ];
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
         $currentTime = \intval( $_SERVER[ 'REQUEST_TIME' ] );
         if ( $currentTime > $refreshTime )
         {
            $cache->delete();
            $cache->flush();
            $this->updateActivityCacheRefreshTime( $refreshTimeFile, $db, $refreshTime, $currentTime );
         }
      }
   }

   protected function updateActivityCacheRefreshTime( $refreshTimeFile, $db, $refreshTime, $currentTime )
   {
      $nextRefreshTime = $currentTime + 604800;
      $sql = 'SELECT start_time, end_time FROM activities WHERE status = 1 AND (start_time > ' . $currentTime . ' OR end_time > ' . $currentTime . ')';
      foreach ( $db->query( $sql ) as $r )
      {
         if ( $r[ 'start_time' ] < $currentTime )
         {
            // current activity
            if ( $r[ 'end_time' ] < $nextRefreshTime )
            {
               $nextRefreshTime = $r[ 'end_time' ];
            }
         }
         else
         {
            // future activity
            if ( $r[ 'start_time' ] < $nextRefreshTime )
            {
               $nextRefreshTime = $r[ 'start_time' ];
            }
         }
      }
      \file_put_contents( $refreshTimeFile, $nextRefreshTime );
   }

// daily at 23:55 CDT
   protected function do_session()
   {
      $db = DB::getInstance( $this->config->db );
      $currentTime = (int) $_SERVER[ 'REQUEST_TIME' ];
      $db->query( 'DELETE FROM sessions WHERE uid = 0 AND atime < ' . ($currentTime - 21600) );
      $db->query( 'DELETE FROM sessions WHERE atime < ' . ($currentTime - $this->config->cookie[ 'lifetime' ]) );
   }

// daily
   protected function do_backup()
   {
      // clean database before backup
      $db = DB::getInstance( $this->config->db );
      $db->query( 'CALL clean()' );
      $cacheTables = [ ];
      foreach ( $db->query( 'SHOW TABLES LIKE "cache_%"' ) as $row )
      {
         $cacheTables[] = \array_shift( $row );
      }
      unset( $db );

      $db = $this->config->db;
      $mysqldump = '/usr/bin/mysqldump';
      $gzip = '/bin/gzip';
      $cmd = $mysqldump . ' --opt --routines --default-character-set=utf8 --set-charset --user=' . $db[ 'user' ] . ' --password=' . $db[ 'password' ] . ' ' . $db[ 'dsn' ];
      foreach ( $cacheTables as $t )
      {
         $cmd = $cmd . ' --ignore-table=' . $db[ 'dsn' ] . '.' . $t;
      }

      $cmd = $cmd . ' | ' . $gzip . ' > ' . $this->config->path[ 'backup' ] . '/' . \date( 'Y-m-d', \intval( $_SERVER[ 'REQUEST_TIME' ] ) - 86400 ) . '.sql.gz';
      echo \shell_exec( $cmd );
   }

   protected function do_ad()
   {
      $db = DB::getInstance( $this->config->db );

      $ads = $db->query( 'SELECT * FROM ads WHERE exp_time < ' . ($this->timestamp + 604800) . ' AND exp_time > ' . ($this->timestamp - 172800) );
      $count = \sizeof( $ads );
      if ( $count > 0 )
      {
         $mailer = new Mailer();
         Template::$path = $this->config->path[ 'theme' ] . '/' . $this->config->theme[ 'roselife' ];

         $mailer->to = $this->config->webmaster;
         $mailer->subject = '[ ' . $count . ' ] 七天内过期广告';
         $contents = [ 'ads' => $ads ];
         $mailer->body = new Template( 'mail/ads', $contents );

         if ( $mailer->send() === FALSE )
         {
            $this->logger->info( 'sending expiring ads email error.' );
         }
      }
   }

   // daily at 23:55 CDT
   protected function do_alexa()
   {
      foreach ( ['houston', 'dallas', 'austin' ] as $city )
      {
         $this->_updateAlexa( $city );
      }
   }

   private function _updateAlexa( $city )
   {
      $c = \curl_init( 'http://data.alexa.com/data?cli=10&dat=s&url=http://www.' . $city . 'bbs.com' );
      \curl_setopt_array( $c, [
         CURLOPT_RETURNTRANSFER => TRUE,
         CURLOPT_CONNECTTIMEOUT => 2,
         CURLOPT_TIMEOUT => 3
      ] );
      $contents = \curl_exec( $c );
      \curl_close( $c );

      if ( $contents )
      {
         \preg_match( '#<POPULARITY URL="(.*?)" TEXT="([0-9]+){1,}"#si', $contents, $p );
         if ( $p[ 2 ] )
         {
            $rank = \number_format( intval( $p[ 2 ] ) );
            $data = \ucfirst( $city ) . 'BBS最近三个月平均访问量<a href="http://www.alexa.com/data/details/main?url=http://www.' . $city . 'bbs.com">Alexa排名</a>:<br /><a href="http://www.alexa.com/data/details/main?url=http://www.' . $city . 'bbs.com">第 <b>' . $rank . '</b> 位</a> (更新时间: ' . \date( 'm/d/Y H:i:s T', \intval( $_SERVER[ 'REQUEST_TIME' ] ) ) . ')';
            \file_put_contents( $this->config->path[ 'theme' ] . '/' . $this->config->theme[ 'roselife' ] . '/alexa.' . $city . '.tpl.php', $data );
         }
         else
         {
            $this->logger->info( 'Get Alexa Rank Error' );
         }
      }
   }
   
   protected function do_checkSpamer()
   {
      $db = DB::getInstance( $this->config->db );

      $users = $db->query( 
         'SELECT id, '
         . '(SELECT COUNT(*) FROM nodes WHERE uid = users.id) AS nc, '
         . '(SELECT COUNT(*) FROM comments WHERE uid = users.id) AS cc, '
         . 'username, email, create_time, last_access_time, last_access_ip '
         . 'FROM users WHERE username = SUBSTRING_INDEX(email,"@",1) AND status = 1' );
      
      foreach( $users as $u )
      {
         // skip usernames with uppercase letters
         if( \preg_match( '/[A-Z]/', $u['username'] ) || !\preg_match( '/[a-z][0-9]+[a-z]/', $u['username'] ) )
         {
            continue;
         }
         
         $geo = \geoip_record_by_name( \long2ip( $u['last_access_ip'] ) );
         if( $geo )
         {
            $city = $geo[ 'city' ] ? $geo[ 'city' ] : 'NA';
            $region = $geo[ 'region' ] ? $geo[ 'region' ] : 'NA';
         }
         
         echo \implode( "\t", [ $u['nc'], $u['cc'], $u['id'], $city , $region, $u['username'], $u['email'] ] ) . \PHP_EOL;

      }
   }

}

//_END_OF_FILE
