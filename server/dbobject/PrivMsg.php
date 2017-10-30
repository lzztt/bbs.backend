<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 *
 * @property $id
 * @property $fromUID
 * @property $toUID
 * @property $msgID
 * @property $time
 * @property $body
 */
class PrivMsg extends DBObject
{
    public function __construct($id = null, $properties = '')
    {
         $db = DB::getInstance();
         $table = 'priv_msgs';
         $fields = [
               'id' => 'id',
               'msgID' => 'msg_id',
               'fromUID' => 'from_uid',
               'toUID' => 'to_uid',
               'time' => 'time',
               'body' => 'body',
         ];
         parent::__construct($db, $table, $fields, $id, $properties);
    }

    public function getPMConversation($id, $uid = 0, $markRead = true)
    {
         return $this->call('get_pm(' . $id . ',' . $uid . ')');
    }

    public function getNewPMCount($uid)
    {
         return $this->call('get_pm_count_new(' . $uid . ')');
    }

    public function getReplyTo($msg_id, $uid)
    {
         return array_pop($this->call('get_pm_replyto(' . $msg_id . ',' . $uid . ')'));
    }

    public function deleteByUser($uid)
    {
         return $this->call('delete_pm(' . $this->id . ',' . $uid . ')');
    }
}

//__END_OF_FILE__
