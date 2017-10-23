<?php

namespace Site;

use lzx\core\ClassLoader;
use lzx\core\Handler;
use lzx\core\Config;
use lzx\core\Logger;
use lzx\core\MySQL;
use lzx\core\Mailer;
use lzx\html\Template;
use lzx\core\Cache;
use site\dataobject\User;

\mb_internal_encoding("UTF-8");

// note: cache path in php and nginx are using servername
$_SERVER['SERVER_NAME'] = 'www.houstonbbs.com';

$domain = 'houstonbbs.com';
$siteDir = \dirname(__DIR__);
$path = [
    'lzx' => $siteDir . '/lzx',
    'root' => $siteDir,
    'log' => $siteDir . '/logs',
    'theme' => $siteDir . '/themes',
    'backup' => $siteDir . '/backup',
];

require_once $path['lzx'] . '/Core/ClassLoader.php';
$loader = ClassLoader::getInstance();
$loader->registerNamespace('lzx', $path['lzx']);
$loader->registerNamespace(__NAMESPACE__, $siteDir);

$logger = Logger::getInstance($path['log']);
$logger->userAgent = 'cli';

Handler::$logger = $logger;
// set ErrorHandler
Handler::setErrorHandler();
// set ExceptionHandler
Handler::setExceptionHandler();

// load site config and class config
$config = Config::getInstance($siteDir . '/config.php');
$config->domain = $domain;
$config->mail->domain = $domain;
$task = $argv[1];

$func = __NAMESPACE__ . '\do_' . $task;

if (!\function_exists($func)) {
    $logger->info('CRON JOB: wrong action : ' . $task);
    exit;
}

$func($path, $logger, $config);

function do_user($path, $logger, $config)
{
    $db = MySQL::getInstance($config->database, true);
    $db->setLogger($logger);

    $user = new User();
    $user->where('uid', 126, '=');
    $users = $user->getList('uid,username,email');

    if (\sizeof($users) > 0) {
        $mailer = new Mailer($config->mail->domain);
        Template::$path = $path['theme'] . '/' . $config->theme . '/' . 'pc';
        foreach ($users as $u) {
            $password = $user->randomPW(); // will send generated password to email

            $mailer->to = 'geekpush@gmail.com';
            $mailer->subject = $u['username'] . ' test';
            $contents = [
                'username' => $u['username'],
                'password' => $password,
                'sitename' => 'HoustonBBS',
                'lang' => $config->lang_default // should get from the user record in DB
            ];
            $mailer->body = 'test email';

            if ($mailer->send() === false) {
                $logger->info('sending new user activation email error: ' . $u['email']);
                continue;
            }
        }
    }
}

function do_node($path, $logger, $config)
{
    /*
      require_once CLS_PATH . 'MySQL.cls.php';

      $db = MySQL::getInstance();

      $nodes = $db->query('SELECT n.nid, n.title, u.username, u.email FROM nodes AS n JOIN users AS u ON n.uid = u.uid WHERE n.status IS NULL');
      $db->query('UPDATE nodes SET status = 1 WHERE status IS NULL');
     */
}

function do_activity($path, $logger, $config)
{
    $cacheKey = 'recentActivities';
    $cache = Cache::getInstance($config->cache_path, 'pc', 'member');
    $cache->setLogger($logger);
    $refreshTimeFile = $path['log'] . '/activity_cache_refresh_time.txt';

    $db = MySQL::getInstance($config->database, true);
    $db->setLogger($logger);

    $activities = $db->query('SELECT a.startTime, n.nid, n.title, u.username, u.email FROM Activity AS a JOIN nodes AS n ON a.nid = n.nid JOIN users AS u ON n.uid = u.uid WHERE a.status IS NULL');
    if (\sizeof($activities) > 0) {
        $mailer = new Mailer($config->mail->domain);
        Template::$path = $path['theme'] . '/' . $config->theme . '/' . 'pc';

        foreach ($activities as $a) {
            $mailer->to = $a['email'];
            $mailer->subject = $a['username'] . ' 的活动详情（已激活）';
            $contents = [
                'nid' => $a['nid'],
                'title' => $a['title'],
                'username' => $a['username'],
                'sitename' => 'HoustonBBS'
            ];
            $mailer->body = new Template('mail/activity', $contents);

            if ($mailer->send() === false) {
                $logger->info('sending new activity activation email error.');
                continue;
            }

            $db->query('UPDATE activities SET status = 1 WHERE nid = ' . $a['nid']);

            $newActivities[] = '[TITLE] ' . $a['title'] . \PHP_EOL . ' [URL] http://www.houstonbbs.com/node/' . $a['nid'];
        }

        // delete cache and reschedule next refresh time
        $cache->delete($cacheKey);
        $refreshTime = \is_readable($refreshTimeFile) ? \intval(\file_get_contents($refreshTimeFile)) : 0;
        $currentTime = \intval($_SERVER['REQUEST_TIME']);
        $newActivityStartTime = $activities[0]['startTime'];
        if ($refreshTime < $currentTime || $refreshTime > $newActivityStartTime) {
            updateActivityCacheRefreshTime($refreshTimeFile, $db, $refreshTime, $currentTime);
        }

        $mailer->to = 'admin@houstonbbs.com';
        $mailer->subject = '[活动] ' . \sizeof($activities) . '个新活动已被系统自动激活';
        $mailer->body = \implode("\n\n", $newActivities);
        $mailer->send();
    } // refresh cache based on the next refresh timestamp
    else {
        $refreshTime = \is_readable($refreshTimeFile) ? \intval(\file_get_contents($refreshTimeFile)) : 0;
        $currentTime = \intval($_SERVER['REQUEST_TIME']);
        if ($currentTime > $refreshTime) {
            $cache->delete($cacheKey);
            updateActivityCacheRefreshTime($refreshTimeFile, $db, $refreshTime, $currentTime);
        }
    }
}

function updateActivityCacheRefreshTime($refreshTimeFile, $db, $refreshTime, $currentTime)
{
    $nextRefreshTime = $currentTime + 604800;
    $sql = 'SELECT startTime, endTime FROM Activity WHERE status = 1 AND (startTime > ' . $currentTime . ' OR endTime > ' . $currentTime . ')';
    foreach ($db->query($sql) as $r) {
        if ($r['startTime'] < $currentTime) {
            // current activity
            if ($r['endTime'] < $nextRefreshTime) {
                $nextRefreshTime = $r['endTime'];
            }
        } else {
            // future activity
            if ($r['startTime'] < $nextRefreshTime) {
                $nextRefreshTime = $r['startTime'];
            }
        }
    }
    \file_put_contents($refreshTimeFile, $nextRefreshTime);
}

// daily at 00:00 CDT
/*
  function do_log()
  {
  $date = date('Y-m-d', TIMESTAMP - 3600);

  $logs = [
  'info' => DATA_PATH . 'logs/info.log',
  'debug' => DATA_PATH . 'logs/debug.log',
  //'error' => ROOT . '/error_log',
  'resource' => DATA_PATH . 'logs/resource.log',
  'sql' => DATA_PATH . 'logs/sql.log'
  );

  foreach ($logs as $k => $f)
  {
  if (is_file($f))
  {
  rename($f, DATA_PATH . 'logs/' . $date . '_' . $k . '.log');
  }
  }
  } */

// daily at 23:55 CDT
function do_session($path, $logger, $config)
{
    $db = MySQL::getInstance($config->database, true);
    $db->setLogger($logger);
    $currentTime = \intval($_SERVER['REQUEST_TIME']);
    $db->query('DELETE FROM Session WHERE uid = 0 AND mtime < ' . ($currentTime - 21600));
    $db->query('DELETE FROM Session WHERE mtime < ' . ($currentTime - $config->cookie->lifetime));
}

// daily
function do_backup($path, $logger, $config)
{
    $db = $config->database;
    $mysqldump = '/usr/bin/mysqldump';
    $gzip = '/bin/gzip';
    $cmd = $mysqldump . ' --opt --routines --default-character-set=utf8 --set-charset'
        . ' --user=' . $db->username . ' --password=' . $db->passwd . ' ' . $db->dbname
        . ' | ' . $gzip . ' > ' . $path['backup'] . '/' . \date('Y-m-d', \intval($_SERVER['REQUEST_TIME']) - 86400) . '.sql.gz';
    echo \shell_exec($cmd);
    /*
      $db = MySQL::getInstance($config->database, TRUE);
      $db->setLogger($logger);

      $db->query('CALL clean()');
      //get all of the tables
      $tables = [);
      $res = $db->query('SHOW TABLES');
      foreach ($res as $row)
      {
      $tables[] = \array_pop($row);
      }

      $date = \date('Y-m-d', \intval($_SERVER['REQUEST_TIME']) - 18000);
      $f = \gzopen($path['backup'] . '/' . $date . '.sql.gz', 'w9');

      \gzwrite($f, 'SET NAMES "utf8";' . "\n\n");

      //cycle through table
      foreach ($tables as $table)
      {
      $sql = 'DROP TABLE IF EXISTS `' . $table . '`;';
      $r = $db->row('SHOW CREATE TABLE ' . $table);
      $sql .= "\n\n" . \array_pop($r) . ";\n\nLOCK TABLES `$table` WRITE;\n";
      \gzwrite($f, $sql);

      $rows = $db->query('SELECT * FROM ' . $table);
      $num_fields = $db->num_fields();
      $types = [);
      foreach ($db->field_type() as $k => $t)
      {
      $types[$k] = ($t == 'int' || $t == 'real') ? 'numeric' : 'string';
      }
      $db->free();

      $bulk = 500;
      $values = [);
      foreach ($rows as $i => $r)
      {
      $vs = [);
      foreach ($r as $k => $v)
      {
      $vs[] = isset($v) ? (($types[$k] == 'numeric') ? $v : $db->str($v)) : 'NULL';
      }
      $values[] = '(' . \implode(',', $vs) . ')';

      // write to file for every $bulk records
      if (($i + 1) % $bulk == 0)
      {
      \gzwrite($f, 'INSERT INTO `' . $table . '` VALUES ' . \implode(',', $values) . ";\n");
      $values = [);
      }
      }

      if (sizeof($values) > 0)
      {
      \gzwrite($f, 'INSERT INTO `' . $table . '` VALUES ' . \implode(',', $values) . ";\n");
      }
      // echo $table . ' : ' . memory_get_usage(TRUE)/1024/1024 . "MB\n";
      \gzwrite($f, "UNLOCK TABLES;\n\n\n");
      }

      $procedures = [);
      $res = $db->query('SHOW PROCEDURE STATUS WHERE Db = DATABASE()');
      foreach ($res as $row)
      {
      $procedures[] = $row['Name'];
      }
      $sql = '';
      foreach ($procedures as $procedure)
      {
      $sql .= 'DROP PROCEDURE IF EXISTS `' . $procedure . '`;';
      $r = $db->row('SHOW CREATE PROCEDURE ' . $procedure);
      $sql .= "\n\ndelimiter //\n" . $r['Create Procedure'] . "//\ndelimiter ;\n\n";
      }

      //save file
      \gzwrite($f, $sql);
      \gzclose($f);
     *
     */
}

// daily at 23:55 CDT
function do_alexa($path, $logger, $config)
{
    $c = \curl_init('http://data.alexa.com/data?cli=10&dat=s&url=http://www.houstonbbs.com');
    \curl_setopt_array($c, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_TIMEOUT => 3
    ]);
    $contents = \curl_exec($c);
    \curl_close($c);

    if ($contents) {
        \preg_match('#<POPULARITY URL="(.*?)" TEXT="([0-9]+){1,}"#si', $contents, $p);
        if ($p[2]) {
            $rank = \number_format(intval($p[2]));
            $data = 'HoustonBBS最近三个月平均访问量<a href="http://www.alexa.com/data/details/main?url=http://www.houstonbbs.com" title="HoustonBBS近三个月的访问量统计">Alexa排名</a>:<br /><a href="/node/5641" title="Houston各中文网站 月访问量 横向比较">第 <b>' . $rank . '</b> 位</a> (更新时间: ' . \date('m/d/Y H:i:s T', \intval($_SERVER['REQUEST_TIME'])) . ')';
            \file_put_contents($path['theme'] . '/' . $config->theme . '/alexa.tpl.php', $data);
        } else {
            $logger->info('Get Alexa Rank Error');
        }
    }
}
