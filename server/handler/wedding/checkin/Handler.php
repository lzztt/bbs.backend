<?php declare(strict_types=1);

namespace site\handler\wedding\checkin;

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

        $a = new WeddingAttendee();
        $a->where('tid', 0, '>');
        list($table_guests, $table_counts, $total) = $this->getTableGuests($a->getList('name,guests,checkin,tid'), 'guests');
        $this->var['body'] = new Template('checkin', ['tables' => $table_guests]);
    }
}
