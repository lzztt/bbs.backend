<?php declare(strict_types=1);

namespace site;

use Exception;
use \lzx\App;
use \lzx\db\DB;
use \lzx\core\Mailer;
use \lzx\html\Template;

// note: cache path in php and nginx are using server_name
$_SERVER['SERVER_NAME'] = 'www.houstonbbs.com';
// $config->domain need http_host
$_SERVER['HTTP_HOST'] = 'www.houstonbbs.com';

$LZXROOT = dirname(__DIR__) . '/lib/lzx';

require_once $LZXROOT . '/App.php';

class MailApp extends App
{
    public function __construct()
    {
        parent::__construct();
        // register current namespaces
        $this->loader->registerNamespace(__NAMESPACE__, dirname(__DIR__) . '/server');

        $this->timestamp = intval($_SERVER['REQUEST_TIME']);
        $this->config = Config::getInstance();
        $this->logger->setUserInfo(['uid' => 'cron', 'umode' => 'cli', 'urole' => 'adm']);
        $this->logger->setDir($this->config->path['log']);
        $this->logger->setEmail($this->config->webmaster);
    }

    public function run($argc, array $argv)
    {
        if ($argc != 1 || empty($argv)) {
            throw new Exception('need the starting user id');
        }

        $uid = $argv[0];
        $db = DB::getInstance($this->config->db, true);
        $users = $db->query('SELECT id, username, email, create_time FROM users WHERE id > ' . $uid . ' and cid = 1 LIMIT 550');
        //$users = $db->query('SELECT id, username, email, create_time FROM users WHERE id = 3');

        if (sizeof($users) > 0) {
            $mailer = new Mailer();
            $mailer->from = 'care';

            $status = [];
            Template::setLogger($this->logger);
            Template::$path = $this->config->path['theme'] . '/' . $this->config->theme['roselife'];

            foreach ($users as $i => $u) {
                $mailer->subject = '您做黄页 我们买单 简爱生活 由此开始';
                $mailer->from = 'yp';
                $mailer->domain = 'houstonbbs.com';
                $mailer->to = $u['email'];
                $contents = [
                    'username' => $u['username'],
                    'time' => $this->time($u['create_time'])
                ];

                $mailer->body = new Template('mail/yp', $contents);

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

            $last = array_pop($users);
            return $last['id'];
        }
    }

    private function time($timestamp)
    {
        $intv = date_diff(new DateTime('now'), new DateTime(date('Y-m-d H:i:s', $timestamp)));
        $days = $intv->days;
        if ($days / 365 > 6) {
            return '六年多以来';
        } elseif ($days / 365 > 5) {
            return '五年多以来';
        } elseif ($days / 365 > 4) {
            return '四年多以来';
        } elseif ($days / 365 > 3) {
            return '三年多以来';
        } elseif ($days / 365 > 2) {
            return '两年多以来';
        } elseif ($days / 365 > 1) {
            return '一年多以来';
        } elseif ($days / 365 > 0.5) {
            return '半年多以来';
        } elseif ($days / 30 > 5) {
            return '五个多月来';
        } elseif ($days / 30 > 4) {
            return '四个多月来';
        } elseif ($days / 30 > 3) {
            return '三个多月来';
        } elseif ($days / 30 > 2) {
            return '两个多月来';
        } elseif ($days / 30 > 1) {
            return '一个多月来';
        }

        return '近期';
    }
}

// main program starts here

$flag = '/tmp/mail/sending';
$lock = '/tmp/mail/lock';
if (file_exists($flag)) {
    if (file_exists($lock)) {
        echo 'unable to get mail sending lock, aborting';
        exit(1);
    } else {
        touch($lock);

        $uid = intval(file_get_contents($flag));
        $app = new MailApp();
        $uid_new = intval($app->run(1, [$uid]));
        if ($uid_new > $uid) {
            file_put_contents($flag, $uid_new);
        }

        unlink($lock);
    }
}
