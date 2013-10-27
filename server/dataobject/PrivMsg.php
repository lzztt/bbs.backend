<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dataobject;

use lzx\core\DataObject;
use lzx\core\MySQL;
use lzx\core\Mailer;
use lzx\core\Logger;

/**
 *
 * @property $mid
 * @property $formUID
 * @property $toUID
 * @property $topicMID
 * @property $time
 * @property $body
 * @property $isNew
 * @property $isDeleted
 */
class PrivMsg extends DataObject
{

   public function __construct($load_id = null, $fields = '')
   {
      $db = MySQL::getInstance();
      parent::__construct($db, 'privmsgs', $load_id, $fields);
   }

   public function add()
   {
      parent::add();

      $sql = 'SELECT u.username, u.email, IFNULL(m.topicMID, m.mid) AS topicMID FROM privmsgs AS m JOIN users AS u ON m.toUID = u.uid WHERE m.mid = ' . $this->mid;
      $msg = $this->_db->row($sql);
      $mailer = new Mailer();
      $mailer->to = $msg['email'];
      $mailer->subject = $msg['username'] . ' 您有一封新的站内短信';
      $mailer->body = $msg['username'] . ' 您有一封新的站内短信' . "\n" . '请登录后点击下面链接阅读' . "\n" . 'http://www.houstonbbs.com/pm/' . $msg['topicMID'];
      if (!$mailer->send())
      {
         $logger = Logger::getInstance();
         $logger->info('PM EMAIL REMINDER SENDING ERROR: ' . $this->mid);
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
     . ' (SELECT isNew FROM privmsgs WHERE topicMID = m.mid AND toUID = ' . $uid . ' ORDER BY time DESC LIMIT 1),' // FROM UID BUT HAS REPLYS
     . ' IFNULL((SELECT isNew FROM privmsgs WHERE topicMID = m.mid AND toUID = ' . $uid . ' ORDER BY time DESC LIMIT 1), m.isNew )' // TO UID, CHECK REPLYS FIRST
     . ') AS isNew'
     . ' FROM ('
     . 'SELECT IFNULL( topicMID, mid ) AS tid FROM privmsgs WHERE toUID = ' . $uid . ' GROUP BY tid ORDER BY time DESC' // TOPIC MID TO UID / FROM UID BUT HAS REPLYS
     . ') AS idTable'
     . ' LEFT JOIN privmsgs AS m ON idTable.tid = m.mid'
     . ' WHERE (m.fromUID = ' . $uid . ' AND m.isDeleted < 2) OR (m.toUID = ' . $uid . ' AND m.isDeleted%2 = 0)'; // isDeleted = FALSE
     return $this->_db->select($sql);
     }
    */

   public function getPMConversation($mid, $uid = 0, $markRead = TRUE)
   {
      $db = $this->_db;

      $sql = 'SELECT m.mid,m.time,m.body,u.uid,u.username, u.avatar'
         . ' FROM privmsgs AS m JOIN users AS u ON m.fromUID = u.uid'
         . ' WHERE m.topicMID = ' . $mid
         . ' AND ((m.fromUID = ' . $uid . ' AND m.isDeleted < 2) OR (m.toUID = ' . $uid . ' AND m.isDeleted%2 = 0))'
         . ' ORDER BY m.time';
      $pm = $db->select($sql);

      if ($markRead)
      {
         $db->query('UPDATE privmsgs SET isNew = 0 WHERE topicMID=' . $mid . ' AND toUID=' . $uid . ' AND isNew = 1');
      }

      return $pm;
   }

   public function getReplyTo($topicMID, $uid)
   {
      $sql_uids = 'SELECT fromUID, toUID FROM privmsgs WHERE topicMID = ' . $topicMID . ' LIMIT 1';
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
         $this->_db->query('UPDATE privmsgs SET isDeleted = isDeleted + 2 WHERE isDeleted < 2 AND fromUID = ' . $this->fromUID . ' AND topicMID = ' . $this->topicMID);
         $this->_db->query('UPDATE privmsgs SET isDeleted = isDeleted + 1 WHERE isDeleted % 2 = 0 AND toUID = ' . $this->fromUID . ' AND topicMID = ' . $this->topicMID);
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
         $this->_db->query('UPDATE privmsgs SET isDeleted = isDeleted + 2 WHERE isDeleted < 2 AND fromUID = ' . $this->toUID . ' AND topicMID = ' . $this->topicMID);
         $this->_db->query('UPDATE privmsgs SET isDeleted = isDeleted + 1 WHERE isDeleted % 2 = 0 AND toUID = ' . $this->toUID . ' AND topicMID = ' . $this->topicMID);
      }
   }

}

//__END_OF_FILE__
