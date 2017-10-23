<?php

namespace site\controller\single;

use site\controller\Single;
use site\dbobject\FFActivity;
use lzx\html\Template;

/**
 * @property \lzx\db\DB $db database object
 */
class ActivitiesCtrler extends Single
{
    public function run()
    {
        $act = new FFActivity();
        $act->order('id', 'DESC');
        $activities = $act->getList();
        \array_shift($activities);

        foreach ($activities as $i => $a) {
            $activities[$i]['chart'] = $this->_getChart($a);
            $activities[$i]['comments'] = $this->_getComments($a['id'], 'ASC');
        }

        $this->_var['content'] = new Template('activities', ['activities' => $activities]);
    }
}

//__END_OF_FILE__
