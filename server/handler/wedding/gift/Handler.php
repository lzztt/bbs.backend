<?php declare(strict_types=1);

namespace site\handler\wedding\gift;

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
        $a = new WeddingAttendee();
        list($table_guests, $table_counts, $total) = $this->getTableGuests($a->getList('name,tid,gift,value,guests,comment'), 'value');

        $this->var['body'] = new Template('gifts', ['tables' => $table_guests, 'counts' => $table_counts, 'total' => $total]);
    }
}
