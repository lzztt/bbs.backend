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
 * @property $from_uid
 * @property $to_uid
 * @property $msgID
 * @property $time
 * @property $body
 * @property $is_new
 * @property $is_deleted
 */
class PrivMsg extends DBObject
{

   public function __construct($id = null, $properties = '')
   {
      $db = DB::getInstance();
      $table = 'priv_msgs';
      $fields = [
         'id' => 'id',
         'from_uid' => 'from_uid',
         'to_uid' => 'to_uid',
         'msgID' => 'msg_id',
         'time' => 'time',
         'body' => 'body',
         'is_new' => 'is_new',
         'is_deleted' => 'is_deleted'
      ];
      parent::__construct( $db, $table, $fields, $id, $properties );
   }

   public function add()
   {
      parent::add();

      $sql = 'SELECT u.username, u.email, IFNULL(m.msg_id, m.id) AS msg_id FROM priv_msgs AS m JOIN users AS u ON m.to_uid = u.id WHERE m.id = ' . $this->id;
      $msg = $this->_db->row($sql);
      $mailer = new Mailer();
      $mailer->to = $msg['email'];
      $mailer->subject = $msg['username'] . ' 您有一封新的站内短信';
      $mailer->body = $msg['username'] . ' 您有一封新的站内短信' . "\n" . '请登录后点击下面链接阅读' . "\n" . 'http://www.houstonbbs.com/pm/' . $msg['msg_id'];
      if (!$mailer->send())
      {
         $logger = Logger::getInstance();
         $logger->error('PM EMAIL REMINDER SENDING ERROR: ' . $this->id);
      }
   }

   /*
     public function getUserPMs($uid)
     {
     $sql = 'SELECT m.id, m.time, m.subject,'
     . ' (SELECT username FROM users WHERE uid = m.from_uid) AS fromName,'
     . ' (SELECT username FROM users WHERE uid = m.to_uid) AS toName, '
     . ' IF('
     . ' m.from_uid = ' . $uid . ','
     . ' (SELECT is_new FROM priv_msgs WHERE msg_id = m.id AND to_uid = ' . $uid . ' ORDER BY time DESC LIMIT 1),' // FROM UID BUT HAS REPLYS
     . ' IFNULL((SELECT is_new FROM priv_msgs WHERE msg_id = m.id AND to_uid = ' . $uid . ' ORDER BY time DESC LIMIT 1), m.is_new )' // TO UID, CHECK REPLYS FIRST
     . ') AS is_new'
     . ' FROM ('
     . 'SELECT IFNULL( msg_id, id ) AS tid FROM priv_msgs WHERE to_uid = ' . $uid . ' GROUP BY tid ORDER BY time DESC' // TOPIC MID TO UID / FROM UID BUT HAS REPLYS
     . ') AS idTable'
     . ' LEFT JOIN priv_msgs AS m ON idTable.tid = m.id'
     . ' WHERE (m.from_uid = ' . $uid . ' AND m.is_deleted < 2) OR (m.to_uid = ' . $uid . ' AND m.is_deleted%2 = 0)'; // is_deleted = FALSE
     return $this->_db->select($sql);
     }
    */

   public function getPMConversation($id, $uid = 0, $markRead = TRUE)
   {
      $db = $this->_db;

      $sql = 'SELECT m.id,m.time,m.body,u.id,u.username, u.avatar'
         . ' FROM priv_msgs AS m JOIN users AS u ON m.from_uid = u.id'
         . ' WHERE m.msg_id = ' . $id
         . ' AND ((m.from_uid = ' . $uid . ' AND m.is_deleted < 2) OR (m.to_uid = ' . $uid . ' AND m.is_deleted%2 = 0))'
         . ' ORDER BY m.time';
      $pm = $db->select($sql);

      if ($markRead)
      {
         $db->update('UPDATE priv_msgs SET is_new = 0 WHERE msg_id=' . $id . ' AND to_uid=' . $uid . ' AND is_new = 1');
      }

      return $pm;
   }

   public function getReplyTo($msg_id, $uid)
   {
      $sql_uids = 'SELECT from_uid, to_uid FROM priv_msgs WHERE msg_id = ' . $msg_id . ' LIMIT 1';
      $sql = 'SELECT id, username FROM users, (' . $sql_uids . ') AS uids WHERE (id = uids.from_uid OR id = uids.to_uid) AND id != ' . $uid;
      return $this->_db->row($sql);
   }

   public function deleteByAuthor()
   {
      $this->load('is_deleted');
      if ($this->is_deleted < 2)
      {
         $this->is_deleted += 2;
         $this->update('is_deleted');
      }
      if ($this->id == $this->msg_id)
      {
         $this->_db->update('UPDATE priv_msgs SET is_deleted = is_deleted + 2 WHERE is_deleted < 2 AND from_uid = ' . $this->from_uid . ' AND msg_id = ' . $this->msg_id);
         $this->_db->update('UPDATE priv_msgs SET is_deleted = is_deleted + 1 WHERE is_deleted % 2 = 0 AND to_uid = ' . $this->from_uid . ' AND msg_id = ' . $this->msg_id);
      }
   }

   public function deleteByRecipient()
   {
      $this->load('is_deleted');
      if ($this->is_deleted % 2 == 0)
      {
         $this->is_deleted += 1;
         $this->update('is_deleted');
      }
      if ($this->id == $this->msg_id)
      {
         $this->_db->update('UPDATE priv_msgs SET is_deleted = is_deleted + 2 WHERE is_deleted < 2 AND from_uid = ' . $this->to_uid . ' AND msg_id = ' . $this->msg_id);
         $this->_db->update('UPDATE priv_msgs SET is_deleted = is_deleted + 1 WHERE is_deleted % 2 = 0 AND to_uid = ' . $this->to_uid . ' AND msg_id = ' . $this->msg_id);
      }
   }

}

//__END_OF_FILE__
