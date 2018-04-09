<?php declare(strict_types=1);

namespace script;

use DateTime;
use lzx\App;
use lzx\core\Mailer;
use lzx\db\DB;
use lzx\html\Template;
use site\Config;
use site\dbobject\User;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// note: cache path in php and nginx are using server_name
$_SERVER['SERVER_NAME'] = 'www.houstonbbs.com';

class MailApp extends App
{
    public function __construct()
    {
        parent::__construct();

        $this->timestamp = intval($_SERVER['REQUEST_TIME']);
        $this->config = Config::getInstance();
        $this->logger->setFile($this->config->path['log'] . '/' . $this->config->domain . '.log');
        $this->logger->setEmail($this->config->webmaster, 'Mail error', 'logger@' . $this->config->domain);
        $this->logger->addExtraInfo(['uid' => 'cron', 'umode' => 'cli', 'urole' => 'adm']);
    }

    public function run(array $args = []): void
    {
        $db = DB::getInstance($this->config->db);
        $arr = $db->query('SELECT uid FROM mails ORDER BY uid DESC limit 1');
        $uid = $arr ? (int) array_pop(array_pop($arr)) : 0;
        $users = $db->query('SELECT id, username, email, create_time, cid FROM users WHERE id > ' . $uid . ' LIMIT 550');
        // $users = $db->query('SELECT id, username, email, create_time, cid FROM users WHERE id > ' . $uid . ' AND email NOT LIKE '%@qq.com' LIMIT 550');
        // $users = $db->query('SELECT id, username, email, create_time, cid FROM users WHERE id in (29634,29641,29644,29647,29675,29689,29701,29704,29707,29714,29726) or (id > 29726 and email like '%@qq.com') order by id');
        // $users = $db->query('SELECT id, username, email, create_time, cid FROM users WHERE id > ' . $uid . ' AND email LIKE '%@qq.com' LIMIT 300');
        /* TEST
        $users = $db->query('SELECT id, username, email, create_time, cid FROM users WHERE id > ' . $uid . ' LIMIT 3');
        foreach ($users as $i => $u)
        {
            $users[$i]['email'] = 'ikki3355@gmail.com';
            $users[$i]['cid'] = $i + 1;
        }
         */

        $cities = ['休斯顿', '达拉斯', '奥斯汀'];
        $domain = ['houston', 'dallas', 'austin'];

        if (sizeof($users) > 0) {
            $status = [];
            Template::setLogger($this->logger);
            Template::$path = $this->config->path['theme'];
            Template::$theme = $this->config->theme;

            foreach ($users as $i => $u) {
                $cid = (int) $u['cid'] - 1;
                $city = $cities[$cid];
                $unsubLink = 'https://www.' . $domain[$cid] . 'bbs.com/unsubscribe?c=' . User::encodeEmail($u['email'], (int) $u['id']);

                $mailer = new Mailer('newyear@' . $domain[$cid] . 'bbs.com');
                $mailer->setSubject('新年快乐，狗年吉祥');
                $mailer->setTo($u['email']);
                $mailer->setUnsubscribe($unsubLink);
                $contents = [
                    'username' => $u['username'],
                    'time' => $this->time((int) $u['create_time']),
                    'city' => $city,
                    'unsubscribeLink' => $unsubLink
                ];

                $mailer->setBody((string) new Template('mail/newyear', $contents), true);

                if ($mailer->send()) {
                    $status[] = '(' . $u['id'] . ', 1)';
                } else {
                    $status[] = '(' . $u['id'] . ', 0)';
                    $this->logger->info('News Letter Email Sending Error: ' . $u['id']);
                    $this->logger->flush();
                }
                if ($i % 100 == 99) {
                    $db->query('INSERT INTO mails (uid, status) values ' . implode(',', $status));
                    $db->flush();
                    $status = [];
                }

                sleep(6);
            }

            if ($status) {
                $db->query('INSERT INTO mails (uid, status) values ' . implode(',', $status));
                $db->flush();
            }
        }
    }

    private function time(int $timestamp)
    {
        $intv = (new DateTime())->diff(new DateTime(date('Y-m-d H:i:s', $timestamp)));
        $nums = ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九', '十'];
        $days = $intv->days;
        $month = round($days / 30);
        if ($month >= 8) {
            $year = round($days / 365);
            return $nums[$year] . '年来';
        } elseif ($month > 0) {
            return $nums[$month] . '个月来';
        }

        return '近期';
    }
}

// main program starts here
$lock = '/tmp/mail/lock';
if (file_exists($lock)) {
    echo 'unable to get mail sending lock, aborting';
    exit(1);
} else {
    touch($lock);

    $app = new MailApp();
    $app->run();

    unlink($lock);
}
