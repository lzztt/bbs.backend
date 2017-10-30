<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace site\controller\wedding;

use site\controller\Wedding;
use lzx\html\Template;
use site\dbobject\Wedding as WeddingAttendee;

/**
 * Description of Wedding
 *
 * @author ikki
 */
class ListAllCtrler extends Wedding
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
        $a = new WeddingAttendee();
        $a->where('tid', 0, '>');
        list($table_guests, $table_counts, $total) = $this->getTableGuests($a->getList('name,tid,guests,email,phone,time,checkin'), 'guests');

        $this->var['body'] = new Template('attendees', ['tables' => $table_guests, 'counts' => $table_counts, 'total' => $total]);
    }
}

//__END_OF_FILE__
