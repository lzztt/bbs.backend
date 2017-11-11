<?php

namespace site\handler\single\checkin;

use site\handler\single\Single;
use site\dbobject\FFAttendee;
use lzx\html\Template;

class Handler extends Single
{
    public function run()
    {
        // login first
        if (!$this->session->loginStatus) {
            $this->displayLogin();
            return;
        }

        // logged in
        $act = array_pop($this->db->query('CALL get_latest_single_activity()'));
        $atd = new FFAttendee();
        $atd->aid = (int) $act['id'];
        $atd->status = 1;

        $confirmed_groups = [[], []];
        $atd->where('checkin', 0, '>');
        $atd->order('checkin');
        foreach ($atd->getList('name,sex') as $attendee) {
            $confirmed_groups[(int) $attendee['sex']][] = $attendee;
        }

        $atd->where('checkin', 0, '=');
        foreach ($atd->getList('id,name,sex') as $attendee) {
            $unconfirmed_groups[(int) $attendee['sex']][] = $attendee;
        }

        $this->var['content'] = new Template('checkin', ['confirmed_groups' => $confirmed_groups, 'unconfirmed_groups' => $unconfirmed_groups]);
    }
}

//__END_OF_FILE__
