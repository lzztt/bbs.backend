<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

class PrivMsg extends DBObject
{
    public $id;
    public $msgId;
    public $fromUid;
    public $toUid;
    public $time;
    public $body;

    public function __construct($id = null, $properties = '')
    {
         $db = DB::getInstance();
         $table = 'priv_msgs';
         parent::__construct($db, $table, $id, $properties);
    }

    public function getPMConversation($id, $uid = 0, $markRead = true)
    {
         return $this->call('get_pm(' . $id . ',' . $uid . ')');
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
