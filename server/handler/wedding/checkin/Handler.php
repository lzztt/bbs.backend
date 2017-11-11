<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace site\handler\wedding\checkin;

use site\handler\wedding\Wedding;
use lzx\html\Template;
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
        Template::$theme = $this->config->theme['wedding2'];
        // login first
        if (!$this->session->loginStatus) {
            $this->displayLogin();
            return;
        }

        $a = new WeddingAttendee();
        $a->where('tid', 0, '>');
        list($table_guests, $table_counts, $total) = $this->getTableGuests($a->getList('name,guests,checkin,tid'), 'guests');
        $this->var['body'] = new Template('checkin', ['tables' => $table_guests]);
    }
}
