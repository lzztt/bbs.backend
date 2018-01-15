<?php declare(strict_types=1);

namespace script;

use lzx\App;
use lzx\cache\Cache;
use lzx\cache\CacheHandler;
use lzx\core\Mailer;
use lzx\db\DB;
use lzx\html\Template;
use site\Config;

// note: cache path in php and nginx are using server_name
$_SERVER['SERVER_NAME'] = 'www.houstonbbs.com';
// $config->domain need http_host
$_SERVER['HTTP_HOST'] = 'www.houstonbbs.com';

class CronApp extends App
{
    protected $timestamp;
    protected $config;
    protected $actions;

    public function __construct()
    {
        parent::__construct();

        $this->timestamp = intval($_SERVER['REQUEST_TIME']);
        $this->config = Config::getInstance();
        $this->logger->setFile($this->config->path['log'] . '/' . $this->config->domain . '.log');
        $this->logger->setEmail($this->config->webmaster, 'web error: ' . $_SERVER['REQUEST_URI'], 'logger@' . $this->config->domain);
        $this->logger->addExtraInfo(['user' => 'cron']);

        $this->actions = [];
        foreach (get_class_methods(__CLASS__) as $method) {
            if (substr($method, 0, 2) == 'do') {
                $this->actions[strtolower(substr($method, 2))] = $method;
            }
        }
    }

    public function run(int $argc, array $argv = []): void
    {
        $task = strtolower($argv[1]);
        $func = $this->actions[$task];

        // for logger mail subject
        $_SERVER['REQUEST_URI'] = 'cron->' . $task;

        if (method_exists($this, $func)) {
            $this->$func();
        } else {
            $this->logger->info('CRON JOB: wrong action : ' . $task);
            exit;
        }
    }

    protected function doActivity(): void
    {
        // config cache
        $db = DB::getInstance($this->config->db);
        $site = 'houston';

        CacheHandler::$path = $this->config->path['cache'];
        $cacheHandler = CacheHandler::getInstance($db);
        $cacheHandler->setCacheTreeTable($cacheHandler->getCacheTreeTable() . '_' . $site);
        $cacheHandler->setCacheEventTable($cacheHandler->getCacheEventTable() . '_' . $site);
        Cache::setHandler($cacheHandler);
        Cache::setLogger($this->logger);

        $cache = $cacheHandler->createCache('recentActivities');
        $refreshTimeFile = $this->config->path['log'] . '/activity_cache_refresh_time.txt';

        $activities = $db->query('SELECT a.start_time, n.id, n.title, u.username, u.email FROM activities AS a JOIN nodes AS n ON a.nid = n.id JOIN users AS u ON n.uid = u.id WHERE a.status IS NULL');
        if (sizeof($activities) > 0) {
            $mailer = new Mailer();
            Template::$path = $this->config->path['theme'] . '/' . $this->config->theme['roselife'];

            foreach ($activities as $a) {
                $mailer->setTo($a['email']);
                $mailer->setSubject($a['username'] . ' 的活动详情（已激活）');
                $contents = [
                    'nid' => $a['id'],
                    'title' => $a['title'],
                    'username' => $a['username'],
                    'domain' => 'www.houstonbbs.com',
                    'sitename' => 'HoustonBBS'
                ];
                $mailer->setBody((string) new Template('mail/activity', $contents));

                if ($mailer->send() === false) {
                    $this->logger->info('sending new activity activation email error.');
                    continue;
                }

                $db->query('UPDATE activities SET status = 1 WHERE nid = ' . $a['id']);

                $newActivities[] = '[TITLE] ' . $a['title'] . PHP_EOL . ' [URL] http://www.houstonbbs.com/node/' . $a['id'];
            }

            // delete cache and reschedule next refresh time
            $cache->delete();
            $cache->flush();
            $refreshTime = is_readable($refreshTimeFile) ? (int) file_get_contents($refreshTimeFile) : 0;
            $currentTime = (int) $_SERVER['REQUEST_TIME'];
            $newActivityStartTime = $activities[0]['start_time'];
            if ($refreshTime < $currentTime || $refreshTime > $newActivityStartTime) {
                $this->updateActivityCacheRefreshTime($refreshTimeFile, $db, $refreshTime, $currentTime);
            }

            $mailer->setTo('admin@houstonbbs.com');
            $mailer->setSubject('[活动] ' . sizeof($activities) . '个新活动已被系统自动激活');
            $mailer->setBody(implode("\n\n", $newActivities));
            $mailer->send();
        } // refresh cache based on the next refresh timestamp
        else {
            $refreshTime = is_readable($refreshTimeFile) ? intval(file_get_contents($refreshTimeFile)) : 0;
            $currentTime = intval($_SERVER['REQUEST_TIME']);
            if ($currentTime > $refreshTime) {
                $cache->delete();
                $cache->flush();
                $this->updateActivityCacheRefreshTime($refreshTimeFile, $db, $refreshTime, $currentTime);
            }
        }
    }

    protected function updateActivityCacheRefreshTime($refreshTimeFile, $db, $refreshTime, $currentTime): void
    {
        $nextRefreshTime = $currentTime + 604800;
        $sql = 'SELECT start_time, end_time FROM activities WHERE status = 1 AND (start_time > ' . $currentTime . ' OR end_time > ' . $currentTime . ')';
        foreach ($db->query($sql) as $r) {
            if ($r['start_time'] < $currentTime) {
                // current activity
                if ($r['end_time'] < $nextRefreshTime) {
                    $nextRefreshTime = $r['end_time'];
                }
            } else {
                // future activity
                if ($r['start_time'] < $nextRefreshTime) {
                    $nextRefreshTime = $r['start_time'];
                }
            }
        }
        file_put_contents($refreshTimeFile, $nextRefreshTime);
    }

// daily at 23:55 CDT
    protected function doSession(): void
    {
        $db = DB::getInstance($this->config->db);
        $currentTime = (int) $_SERVER['REQUEST_TIME'];
        $db->query('DELETE FROM sessions WHERE uid = 0 AND atime < ' . ($currentTime - 21600));
        $db->query('DELETE FROM sessions WHERE atime < ' . ($currentTime - $this->config->cookie['lifetime']));
    }

// daily
    protected function doBackup(): void
    {
        // clean database before backup
        $db = DB::getInstance($this->config->db);
        $db->query('CALL clean()');
        $cacheTables = [];
        foreach ($db->query('SHOW TABLES LIKE "cache_%"') as $row) {
            $cacheTables[] = array_shift($row);
        }
        unset($db);

        $db = $this->config->db;
        $mysqldump = '/usr/bin/mysqldump';
        $gzip = '/bin/gzip';
        $cmd = $mysqldump . ' --opt --hex-blob --routines --default-character-set=utf8 --set-charset ' . $db['dsn'];
        foreach ($cacheTables as $t) {
            $cmd = $cmd . ' --ignore-table=' . $db['dsn'] . '.' . $t;
        }

        $cmd = $cmd . ' | ' . $gzip . ' > ' . $this->config->path['backup'] . '/' . date('Y-m-d', intval($_SERVER['REQUEST_TIME']) - 86400) . '.sql.gz';
        echo shell_exec($cmd);
    }

    protected function doAd(): void
    {
        $db = DB::getInstance($this->config->db);

        $mailer = new Mailer('ad');
        $mailer->setBcc($this->config->webmaster);
        Template::$path = $this->config->path['theme'] . '/' . $this->config->theme['roselife'];

        // expiring in seven days
        $ads = $db->query('SELECT * FROM ads WHERE exp_time > ' . ($this->timestamp + 518400) . ' AND exp_time < ' . ($this->timestamp + 604800));
        foreach ($ads as $ad) {
            $this->notifyAdUser($mailer, $ad, '七天内');
        }

        // expiring in one day
        $ads = $db->query('SELECT * FROM ads WHERE exp_time > ' . ($this->timestamp - 86400) . ' AND exp_time < ' . ($this->timestamp));
        foreach ($ads as $ad) {
            $this->notifyAdUser($mailer, $ad, '今天');
        }
    }

    private function notifyAdUser(Mailer $mailer, array $ad, string $time): void
    {
        $mailer->setSubject($ad['name'] . '在HoustonBBS的' . ($ad['type_id'] == 1 ? '电子黄页' : '页顶广告') . $time . '到期');
        $mailer->setTo($ad['email']);
        $mailer->setBody((string) new Template('mail/ad', ['ad' => $ad]));

        if ($mailer->send() === false) {
            $this->logger->info('sending expiring ads email error.');
        }
    }
}
