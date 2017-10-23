<?php

namespace site\controller\sendmail;

use site\controller\SendMail;
use lzx\html\Template;
use lzx\core\Mailer;
use site\dbobject\Email;

class SendMailCtrler extends SendMail
{
    public function run()
    {
        if ($this->request->uid != self::UID_ADMIN) {
            $this->pageNotFound();
        }

        // send email if has post data
        if ($this->request->post) {
            $message = $this->send($this->request->post);
        }

        // display page
        $this->_var['content'] = new Template('send_mail', ['message' => $message]);
    }

    public function send(array $post)
    {
        if (!\filter_var($post['email'], \FILTER_VALIDATE_EMAIL)) {
            $this->error('不合法的电子邮箱 : ' . $post['email']);
        }

        $email = new Email($post['email']);

        if (!$email->exists()) {
            $mailer = new Mailer('care');
            $mailer->to = $post['email'];

            $cid = $this->session->getCityID();
            $mailer->subject = ($cid === 1 ? '缤纷休斯顿华人论坛期待与您的合作' : 'DallasBBS 达拉斯地区免费信息发布');
            $mailer->body = ($cid === 1 ? new Template('mail/houston_ad_email') : new Template('mail/dallas_email', ['name' => $post['name']]));

            if ($mailer->send() === false) {
                $this->error('FAIL: ' . $post['email'] . ' : ' . $post['name']);
            }

            $email->email = $post['email'];
            $email->name = $post['name'];
            $email->time = $this->request->timestamp;
            $email->cid = $cid;
            $email->add();

            return 'SUCCESS: ' . $post['email'] . ' : ' . $post['name'];
        } else {
            return 'SKIP: ' . $post['email'] . ' : ' . $post['name'];
        }
    }
}

//__END_OF_FILE__
