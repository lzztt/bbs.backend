<?php

namespace site\handler\single\activities;

use site\handler\single\Single;
use site\dbobject\FFActivity;
use lzx\html\Template;

/**
 * @property \lzx\db\DB $db database object
 */
class Handler extends Single
{
    public function run()
    {
        $act = new FFActivity();
        $act->order('id', 'DESC');
        $activities = $act->getList();
        array_shift($activities);

        foreach ($activities as $i => $a) {
            $activities[$i]['chart'] = $this->getChart($a);
            $activities[$i]['comments'] = $this->getComments($a['id'], 'ASC');
        }

        $this->var['content'] = new Template('activities', ['activities' => $activities]);
    }
}
