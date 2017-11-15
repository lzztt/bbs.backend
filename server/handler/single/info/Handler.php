<?php declare(strict_types=1);

namespace site\handler\single\info;

use site\handler\single\Single;
use lzx\html\Template;
use site\dbobject\FFAttendee;
use site\dbobject\FFQuestion;
use lzx\core\Mailer;

/**
 * @property \lzx\db\DB $db database object
 */
class Handler extends Single
{
    // private attendee info
    public function run()
    {
        $uid = (int) $this->request->get['u'];
        // not a user request
        if ($uid == 0) {
            $action = $this->args[0];
            if ($action && method_exists($this, $action)) {
                // login first
                if (!$this->session->loginStatus) {
                    $this->displayLogin();
                    return;
                }

                // logged in
                $this->$action();
                return;
            } else {
                $this->pageForbidden();
            }
        }
        //verify user's access code
        $code = $this->request->get['c'];
        if ($code != $this->getCode($uid)) {
            $this->pageForbidden();
        }

        // process the form
        if ($this->request->post) {
            foreach ($this->request->post['question'] as $q) {
                if ($q) {
                    $question = new FFQuestion();
                    $question->aid = (int) $this->request->post['uid'];
                    $question->body = $q;
                    $question->add();
                }
            }

            if ($this->request->post['info']) {
                $a = new FFAttendee();
                $a->id = $this->request->post['uid'];
                $a->info = $this->request->post['info'];
                $a->update('info');
            }

            $this->var['content'] = '<div id="activity">您的信息已经被保存。<br /><a href="/single">返回首页</a></div>';
        } else {
            $this->var['content'] = new Template('info', ['uid' => $uid, 'action' => $this->request->uri]);
        }
    }

    protected function mail()
    {
        // check the email flag
        if (!file_exists('/tmp/single_mail')) {
            $this->pageForbidden();
        }

        $act = array_pop($this->db->query('CALL get_latest_single_activity()'));

        $attendee = new FFAttendee();
        $attendee->aid = $act['id'];
        $mailer = new Mailer();
        $mailer->subject = '七夕单身聚会 活动详情 (本周六下午)';
        foreach ($attendee->getList('id,name,email') as $a) {
            $url = 'https://www.houstonbbs.com/single/info?u=' . $a['id'] . '&c=' . $this->getCode($a['id']);
            $mailer->body = new Template('mail/attendee_final', ['name' => $a['name'], 'url' => $url]);
            $mailer->to = $a['email'];
            $mailer->send();
            sleep(1);
        }

        $mailer->to = 'ikki3355@gmail.com';
        $mailer->send();

        $this->var['content'] = '<div id="activity">email sent<br /><a href="/single">返回首页</a></div>';
    }
}
