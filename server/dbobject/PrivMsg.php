<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class PrivMsg extends DBObject
{
    public $id;
    public $msgId;
    public $fromUid;
    public $toUid;
    public $time;
    public $body;

    public function __construct(int $id = 0, string $properties = '')
    {
         $db = DB::getInstance();
         $table = 'priv_msgs';
         parent::__construct($db, $table, $id, $properties);
    }

    public function getPMConversation(int $id, int $uid = 0, bool $markRead = true): array
    {
         return $this->call('get_pm(' . $id . ',' . $uid . ')');
    }

    public function getReplyTo(int $msg_id, int $uid): array
    {
         return array_pop($this->call('get_pm_replyto(' . $msg_id . ',' . $uid . ')'));
    }

    public function deleteByUser(int $uid): void
    {
         $this->call('delete_pm(' . $this->id . ',' . $uid . ')');
    }
}
