<?php

declare(strict_types=1);

namespace site;

use Exception;
use lzx\cache\CacheHandler;
use lzx\core\Mailer;
use lzx\db\DB;
use site\dbobject\Comment;
use site\dbobject\RentThread;
use site\dbobject\User;
use site\gen\theme\roselife\mail\Activity;
use site\gen\theme\roselife\mail\Ad;
use site\gen\theme\roselife\mail\RentThread as MailRentThread;

class CronHandler extends Handler
{
    public function run(): void
    {
        $this->staticInit();

        $this->actions = [];
        foreach (get_class_methods(__CLASS__) as $method) {
            if (substr($method, 0, 2) === 'do') {
                $this->actions[strtolower(substr($method, 2))] = $method;
            }
        }

        $task = strtolower($this->request->uri);
        $func = $this->actions[$task];
        if (method_exists($this, $func)) {
            $this->$func();
        } else {
            $this->logger->info('CRON JOB: wrong action : ' . $task);
            exit;
        }
    }

    protected function doThread(): void
    {
        date_default_timezone_set('America/Los_Angeles');
        $db = DB::getInstance();
        $mailer = new Mailer();
        $mailer->setTo($this->config->webmaster);
        $thread = new RentThread();
        $thread->status = 'fetched';
        foreach ($thread->getList() as $t) {
            $t['createTime'] = date('m/d H:i', intval($t['createTime']));
            if ($t['images']) {
                $t['images'] = json_decode($t['images'], true);
            } else {
                $t['images'] = [];
            }
            $mailer->setSubject($t['site'] . ': ' . $t['author']);
            $mailer->setBody(
                (string) (new MailRentThread())
                    ->setTitle($t['title'])
                    ->setAuthor($t['author'])
                    ->setBody($t['body'])
                    ->setCreateTime($t['createTime'])
                    ->setImages($t['images'])
                    ->setSite($t['site'])
                    ->setTid($t['tid'])
                    ->setType($t['type']),
                true
            );
            $mailer->send();
            $db->query('UPDATE rent_threads SET status = "contacted" WHERE id = ' . $t['id']);
        }
    }

    protected function doActivity(): void
    {
        // config cache
        $db = DB::getInstance();
        $site = 'houston';

        $cacheHandler = CacheHandler::getInstance();
        $cacheHandler->setPath($this->config->path['cache']);
        $cacheHandler->setDomain($site);
        $cacheHandler->setLogger($this->logger);

        $cache = $cacheHandler->createCache('recentActivities');
        $refreshTimeFile = $this->config->path['log'] . '/activity_cache_refresh_time.txt';

        $activities = $db->query('SELECT a.start_time, n.id, n.title, u.username, u.email FROM activities AS a JOIN nodes AS n ON a.nid = n.id JOIN users AS u ON n.uid = u.id WHERE a.status IS NULL');
        if (sizeof($activities) > 0) {
            $mailer = new Mailer();

            foreach ($activities as $a) {
                $mailer->setTo($a['email']);
                $mailer->setSubject($a['username'] . ' 的活动详情（已激活）');
                $mailer->setBody(
                    (string) (new Activity())
                        ->setNid((int) $a['id'])
                        ->setTitle($a['title'])
                        ->setUsername($a['username'])
                        ->setDomain('www.' . self::$city->domain)
                        ->setSitename('HoustonBBS')
                );

                if ($mailer->send() === false) {
                    $this->logger->info('sending new activity activation email error.');
                    continue;
                }

                $db->query('UPDATE activities SET status = 1 WHERE nid = ' . $a['id']);

                $newActivities[] = '[TITLE] ' . $a['title'] . PHP_EOL . ' [URL] https://www.' . self::$city->domain . '/node/' . $a['id'];
            }

            // delete cache and reschedule next refresh time
            $cache->delete();
            $cache->flush();
            $refreshTime = is_readable($refreshTimeFile) ? (int) file_get_contents($refreshTimeFile) : 0;
            $currentTime = $this->request->timestamp;
            $newActivityStartTime = $activities[0]['start_time'];
            if ($refreshTime < $currentTime || $refreshTime > $newActivityStartTime) {
                $this->updateActivityCacheRefreshTime($refreshTimeFile, $db, $refreshTime, $currentTime);
            }

            $mailer->setTo('admin@' . self::$city->domain);
            $mailer->setSubject('[活动] ' . sizeof($activities) . '个新活动已被系统自动激活');
            $mailer->setBody(implode("\n\n", $newActivities));
            $mailer->send();
        } else {
            // refresh cache based on the next refresh timestamp
            $refreshTime = is_readable($refreshTimeFile) ? intval(file_get_contents($refreshTimeFile)) : 0;
            $currentTime = $this->request->timestamp;
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

    // daily
    protected function doUpdateComplaints(): void
    {
        $db = DB::getInstance();
        $sql = '
        SELECT nc.id, nc.nid, n.title, nc.uid, nc.reporter_uid
        FROM node_complaints AS nc
            JOIN comments AS c ON nc.cid = c.id
            JOIN nodes AS n ON nc.nid = n.id
        WHERE nc.status = 1 AND n.status = 1
            AND c.reportable_until < ' . $this->request->timestamp . ';';

        $ids = [];
        foreach ($db->query($sql) as $r) {
            $user = new User((int) $r['reporter_uid'], 'reputation,contribution');
            $user->contribution -= 1;
            $user->update();
            if ($user->reputation + $user->contribution < -2) {
                $this->logoutUser($user->id);
            }
            // send pm
            $this->sendMessage(
                $user->id,
                '举报失败，您损失了1点贡献。' . PHP_EOL
                    . '原因：您的举报未获得足够的用户支持。' . PHP_EOL
                    . '话题：[' . $r['title'] . '](/node/' . $r['nid'] . ')'
            );
            $ids[] = (int) $r['id'];
        }

        if (!$ids) {
            return;
        }

        $sql = '
        UPDATE node_complaints
        SET status = 0
        WHERE id IN (' . implode(',', $ids) . ');';
        $db->query($sql);
    }

    // daily
    protected function doBackup(): void
    {
        // clean database before backup
        $db = DB::getInstance();
        $db->query('CALL clean()');
        unset($db);

        $db = $this->config->db;
        $mysqldump = '/usr/bin/mysqldump';
        $gzip = '/bin/gzip';
        $cmd = $mysqldump . ' --opt --skip-lock-tables --single-transaction --hex-blob --routines --default-character-set=utf8mb4 --set-charset ' . $db['dsn'];

        $cmd = $cmd . ' | ' . $gzip . ' > ' . $this->config->path['backup'] . '/' . date('Y-m-d', $this->request->timestamp - 86400) . '.sql.gz';
        echo shell_exec($cmd);
    }

    protected function doVerifyPage(): void
    {
        $body = self::curlGet("https://www.houstonbbs.com/");
        if (!$body) {
            throw new Exception('empty home page');
        }

        $file = '/tmp/cache_' . mt_rand();
        file_put_contents($file, $body);
        $tags = get_meta_tags($file);
        unlink($file);
        if (!$tags) {
            $this->logger->info('skip page cache check');
            return;
        }

        $pageTime = (int) $tags['mtime'];

        $db = DB::getInstance();
        $sql = '
        SELECT MAX(c.create_time) AS time
        FROM comments AS c
            JOIN nodes AS n ON n.id = c.nid
        WHERE c.tid < 127
            AND n.status = 1
            AND c.create_time >' . ($this->request->timestamp - 3600);
        $rows = $db->query($sql);
        $dbTime = $rows ? (int) array_pop(array_pop($rows)) : 0;

        if ($dbTime > $pageTime + 3) {
            echo shell_exec('/bin/bash $HOME/clear_cache.sh');
            throw new Exception('stale home page: db=' . $dbTime . ' page=' . $pageTime);
        }
    }

    protected function doAd(): void
    {
        $db = DB::getInstance();

        $mailer = new Mailer('ad');
        $mailer->setBcc($this->config->webmaster);

        // expiring in seven days
        $ads = $db->query('SELECT * FROM ads WHERE exp_time > ' . ($this->request->timestamp + 518400) . ' AND exp_time < ' . ($this->request->timestamp + 604800));
        foreach ($ads as $ad) {
            $this->notifyAdUser($mailer, $ad, '七天内');
        }

        // expiring in one day
        $ads = $db->query('SELECT * FROM ads WHERE exp_time > ' . ($this->request->timestamp - 86400) . ' AND exp_time < ' . ($this->request->timestamp));
        foreach ($ads as $ad) {
            $this->notifyAdUser($mailer, $ad, '今天');
        }
    }

    private function notifyAdUser(Mailer $mailer, array $ad, string $time): void
    {
        $mailer->setSubject($ad['name'] . '在HoustonBBS的' . ($ad['type_id'] == 1 ? '电子黄页' : '页顶广告') . $time . '到期');
        $mailer->setTo($ad['email']);
        $mailer->setBody(
            (string) (new Ad())
                ->setAd($ad)
        );

        if ($mailer->send() === false) {
            $this->logger->error('sending expiring ads email error.');
        }
    }

    protected function doBackfill(): void
    {
        $comment = new Comment();
        $comment->where('body', '%[/%', 'LIKE');
        $comment->where('id', [5537, 50983, 498094], 'NOT IN');

        foreach ($comment->getList('id,body') as $c) {
            $cmnt = new Comment((int) $c['id'], 'id');
            // $cmnt->body = BBCodeRE::parse($c['body']);
            // $cmnt->update('body');
        }
    }
}
