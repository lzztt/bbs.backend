<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;
use lzx\core\Mailer;
use lzx\core\Logger;

/**
 *
 * @property $id
 * @property $fromUID
 * @property $toUID
 * @property $msgID
 * @property $time
 * @property $body
 * @property $isNew
 * @property $isDeleted
 */
class PrivMsg extends DBObject
{

   public function __construct($id = null, $fields = '')
   {
      $db = DB::getInstance();
      $table = 'priv_msgs';
      $feilds = [
         'id' => 'id',
         'fromUID' => 'from_uid',
         'toUID' => 'to_uid',
         'msgID' => 'msg_id',
         'time' => 'time',
         'body' => 'body',
         'isNew' => 'is_new',
         'isDeleted' => 'is_deleted'
      ];
      parent::__construct( $db, $table, $feilds, $id, $fields );
   }

   public function add()
   {
      parent::add();

      $sql = 'SELECT u.username, u.email, IFNULL(m.topicMID, m.mid) AS topicMID FROM priv_msgs AS m JOIN users AS u ON m.toUID = u.uid WHERE m.mid = ' . $this->mid;
      $msg = $this->_db->row($sql);
      $mailer = new Mailer();
      $mailer->to = $msg['email'];
      $mailer->subject = $msg['username'] . ' 您有一封新的站内短信';
      $mailer->body = $msg['username'] . ' 您有一封新的站内短信' . "\n" . '请登录后点击下面链接阅读' . "\n" . 'http://www.houstonbbs.com/pm/' . $msg['topicMID'];
      if (!$mailer->send())
      {
         $logger = Logger::getInstance();
         $logger->error('PM EMAIL REMINDER SENDING ERROR: ' . $this->mid);
      }
   }

   /*
     public function getUserPMs($uid)
     {
     $sql = 'SELECT m.mid, m.time, m.subject,'
     . ' (SELECT username FROM users WHERE uid = m.fromUID) AS fromName,'
     . ' (SELECT username FROM users WHERE uid = m.toUID) AS toName, '
     . ' IF('
     . ' m.fromUID = ' . $uid . ','
     . ' (SELECT isNew FROM priv_msgs WHERE topicMID = m.mid AND toUID = ' . $uid . ' ORDER BY time DESC LIMIT 1),' // FROM UID BUT HAS REPLYS
     . ' IFNULL((SELECT isNew FROM priv_msgs WHERE topicMID = m.mid AND toUID = ' . $uid . ' ORDER BY time DESC LIMIT 1), m.isNew )' // TO UID, CHECK REPLYS FIRST
     . ') AS isNew'
     . ' FROM ('
     . 'SELECT IFNULL( topicMID, mid ) AS tid FROM priv_msgs WHERE toUID = ' . $uid . ' GROUP BY tid ORDER BY time DESC' // TOPIC MID TO UID / FROM UID BUT HAS REPLYS
     . ') AS idTable'
     . ' LEFT JOIN priv_msgs AS m ON idTable.tid = m.mid'
     . ' WHERE (m.fromUID = ' . $uid . ' AND m.isDeleted < 2) OR (m.toUID = ' . $uid . ' AND m.isDeleted%2 = 0)'; // isDeleted = FALSE
     return $this->_db->select($sql);
     }
    */

   public function getPMConversation($mid, $uid = 0, $markRead = TRUE)
   {
      $db = $this->_db;

      $sql = 'SELECT m.mid,m.time,m.body,u.uid,u.username, u.avatar'
         . ' FROM priv_msgs AS m JOIN users AS u ON m.fromUID = u.uid'
         . ' WHERE m.topicMID = ' . $mid
         . ' AND ((m.fromUID = ' . $uid . ' AND m.isDeleted < 2) OR (m.toUID = ' . $uid . ' AND m.isDeleted%2 = 0))'
         . ' ORDER BY m.time';
      $pm = $db->select($sql);

      if ($markRead)
      {
         $db->query('UPDATE PrivMsg SET isNew = 0 WHERE topicMID=' . $mid . ' AND toUID=' . $uid . ' AND isNew = 1');
      }

      return $pm;
   }

   public function getReplyTo($topicMID, $uid)
   {
      $sql_uids = 'SELECT fromUID, toUID FROM priv_msgs WHERE topicMID = ' . $topicMID . ' LIMIT 1';
      $sql = 'SELECT uid, username FROM users, (' . $sql_uids . ') AS uids WHERE (uid = uids.fromUID OR uid = uids.toUID) AND uid != ' . $uid;
      return $this->_db->row($sql);
   }

   public function deleteByAuthor()
   {
      $this->load('isDeleted');
      if ($this->isDeleted < 2)
      {
         $this->isDeleted += 2;
         $this->update('isDeleted');
      }
      if ($this->mid == $this->topicMID)
      {
         $this->_db->query('UPDATE PrivMsg SET isDeleted = isDeleted + 2 WHERE isDeleted < 2 AND fromUID = ' . $this->fromUID . ' AND topicMID = ' . $this->topicMID);
         $this->_db->query('UPDATE PrivMsg SET isDeleted = isDeleted + 1 WHERE isDeleted % 2 = 0 AND toUID = ' . $this->fromUID . ' AND topicMID = ' . $this->topicMID);
      }
   }

   public function deleteByRecipient()
   {
      $this->load('isDeleted');
      if ($this->isDeleted % 2 == 0)
      {
         $this->isDeleted += 1;
         $this->update('isDeleted');
      }
      if ($this->mid == $this->topicMID)
      {
         $this->_db->query('UPDATE PrivMsg SET isDeleted = isDeleted + 2 WHERE isDeleted < 2 AND fromUID = ' . $this->toUID . ' AND topicMID = ' . $this->topicMID);
         $this->_db->query('UPDATE PrivMsg SET isDeleted = isDeleted + 1 WHERE isDeleted % 2 = 0 AND toUID = ' . $this->toUID . ' AND topicMID = ' . $this->topicMID);
      }
   }

}

//__END_OF_FILE__
