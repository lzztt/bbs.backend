<?php

namespace site\controller\single;

use site\controller\Single;
use lzx\html\Template;
use site\dbobject\FFAttendee;
use site\dbobject\FFQuestion;

/**
 * @property \lzx\db\DB $db database object
 */
class ListCtrler extends Single
{
    // private attendee info
    public function run()
    {
        // login first
        if (!$this->session->loginStatus) {
            $this->_displayLogin();
            return;
        }

        // logged in
        if (true) {//$this->request->timestamp < strtotime( "09/16/2013 22:00:00 CDT" ) )
            $act = \array_pop($this->db->query('CALL get_latest_single_activity()'));
            $atd = new FFAttendee();
            $atd->aid = (int) $act['id'];
            $atd->status = 1;

            $groups = [[], []];
            $question = new FFQuestion();
            foreach ($atd->getList('id,name,sex,email,info') as $attendee) {
                $question->aid = $attendee['id'];

                $attendee['questions'] = \array_slice(\array_column($question->getList('body'), 'body'), -3);
                \array_walk($attendee['questions'], function (&$q) {
                    $q = ' - ' . $q;
                });
                $groups[(int) $attendee['sex']][] = $attendee;
            }

            $this->_var['content'] = new Template('attendees', ['groups' => $groups]);
        } else {
            $this->_var['content'] = "<p>ERROR: The page you request is not available anymore</p>";
        }
    }
}

//__END_OF_FILE__
