<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace site\handler\wedding\join;

use site\handler\wedding\Wedding;
use lzx\html\Template;
use lzx\core\Mailer;
use site\dbobject\Wedding as WeddingAttendee;

/**
 * Description of Wedding
 *
 * @author ikki
 */
class Handler extends Wedding
{
    public function run()
    {
        $a = new WeddingAttendee();
        $a->name = $this->request->post['name'];
        $a->email = $this->request->post['email'];
        $a->phone = $this->request->post['phone'];
        $a->guests = $this->request->post['count'];
        $a->time = $this->request->timestamp;
        $a->status = 1;
        if ($this->register_end) {
            $days = ceil(( $this->request->timestamp - strtotime('2014/06/19') ) / 86400);
        } else {
            $a->add();
        }

        $mailer = new Mailer('wedding');
        $mailer->subject = 'wedding: ' . $a->name . ' ( ' . $a->guests . ' )';
        if ($this->register_end) {
            $mailer->subject = 'LATE ' . $days . ' DAYS : ' . $mailer->subject;
        }
        $mailer->body = (string) $a; //new Template('wedding_mail');
        $mailer->signature = '';
        $mailer->to = 'admin@houstonbbs.com';
        $mailer->send();

        if ($this->register_end) {
            $this->error('迟到' . $days . '天的报名 :(<br />新婚答谢宴报名已于2014年6月18号(星期三)结束');
        }

        $mailer->subject = '谢谢来参加我们的新婚答谢宴';
        $mailer->to = $a->email;
        $mailer->body = new Template('mail/attendee', ['name' => $a->name]);
        $mailer->send();

        $this->var['body'] = '<div class="center">谢谢' . $a->name . '!</div>'
            . '<div class="down">新婚答谢宴将于6月28日晚上6点28分举行<br />地点等详情已经通过email发送到您的邮箱 ' . $a->email . '<br />请查收~</div>';
    }
}

//__END_OF_FILE__
