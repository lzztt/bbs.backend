<?php declare(strict_types=1);

namespace site\handler\wedding\add;

use site\handler\wedding\Wedding;
use lzx\html\Template;
use site\dbobject\Wedding as WeddingAttendee;

class Handler extends Wedding
{
    public function run()
    {
        Template::$theme = $this->config->theme['wedding2'];
        // login first
        if (!$this->session->loginStatus) {
            $this->displayLogin();
            return;
        }

        // logged in
        $this->var['navbar'] = new Template('navbar');
        if ($this->request->post) {
            // save changes for one guest
            $a = new WeddingAttendee();

            foreach ($this->request->post as $k => $v) {
                $a->$k = $v;
            }
            $a->time = $this->request->timestamp;
            $a->status = 1;
            $a->add();
            $this->var['body'] = $a->name . '已经被添加';
        } else {
            $this->var['body'] = new Template('join_form');
        }
    }
}
