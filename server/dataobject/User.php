<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dataobject;

use lzx\core\DataObject;
use lzx\core\MySQL;
use lzx\core\PasswordHash;

/**
 * @property $uid
 * @property $username
 * @property $password
 * @property $email
 * @property $msn
 * @property $qq
 * @property $website
 * @property $firstName
 * @property $lastName
 * @property $sex
 * @property $birthday
 * @property $location
 * @property $occupation
 * @property $interests
 * @property $favoriteQuotation
 * @property $relationship
 * @property $signature
 * @property $createTime
 * @property $lastAccessTime
 * @property $lastAccessIP
 * @property $status
 * @property $timezone
 * @property $avatar
 * @property $type
 * @property $role
 * @property $badge
 * @property $points
 * @property $phpass
 */
class User extends DataObject
{
   
   public function __construct($load_id = null, $fields = '')
   {
      $db = MySQL::getInstance();
      $table = \array_pop( \explode( '\\', __CLASS__ ) );
      parent::__construct( $db, $table, $load_id, $fields );
   }

   public function hashPW($password)
   {
      return \md5('Alex' . $password . 'Tian');
   }

   public function randomPW()
   {
      $chars = 'aABCdEeFfGHiKLMmNPRrSTWXY23456789@#$=';
      $salt = \substr(\str_shuffle($chars), 0, 3);
      return $salt . \substr(\str_shuffle($chars), 0, 7); // will send generated password to email
   }

   public function isSuperUser($uid, $cid)
   {
      return in_array($uid, array(
         1
      ));
   }

   public function login($username, $password)
   {
      $this->username = $username;
      $this->load('uid,status');
      if ($this->exists() && $this->status == 1)
      {
         $hash = $this->hashPW($password);
         $this->password = $hash;
         $this->load('uid');
         if ($this->exists())
         {
            return TRUE;
         }

         $this->password = md5($password);
         $this->load('uid');
         if ($this->exists())
         {
            $sql = 'UPDATE ' . $this->_table
               . ' SET phpass = NULL, password = ' . $this->_db->str($hash)
               . ' WHERE uid = ' . $this->uid;
            $this->_db->query($sql);
            return TRUE;
         }

         $this->password = null;
         $phpass = new PasswordHash(8, TRUE);
         $this->load('uid,phpass');
         if ($phpass->CheckPassword($password, $this->phpass))
         {
            $sql = 'UPDATE ' . $this->_table
               . ' SET phpass = NULL, password = ' . $this->_db->str($hash)
               . ' WHERE uid = ' . $this->uid;
            $this->_db->query($sql);
            return TRUE;
         }

         return FALSE;
      }

      return FALSE;
   }

   /*
    * delete nodes and return the node ids whose cache need to be deleted
    */

   public function delete()
   {
      $this->status = 0;
      $this->update('status');
      $this->_db->query('DELETE FROM Session WHERE uid = ' . $this->uid);
      $this->_db->query('UPDATE Node SET status = 0 WHERE uid = ' . $this->uid);
      $this->_db->query('INSERT INTO Spammer (email, ipInt, time) SELECT email, lastAccessIPInt, UNIX_TIMESTAMP() FROM User WHERE uid = ' . $this->uid);
      //$this->_db->query('DELETE FROM Comment WHERE uid = ' . $this->uid);
   }

   public function getAllNodeIDs()
   {
      $nids = array();
      if ($this->uid > 1)
      {
         foreach ($this->_db->select('SELECT nid FROM Node WHERE uid = ' . $this->uid) as $n)
         {
            $nids[] = $n['nid'];
         }
      }
      return $nids;
   }

   public function checkSpamEmail($email)
   {
      $count = $this->_db->val('SELECT COUNT(*) FROM Spammer WHERE email = ' . $this->_db->str($email));
      return ($count > 0 ? FALSE : TRUE);
   }

   public function getRecentNodes($limit)
   {
      return $this->_db->select('SELECT nid, title, createTime FROM Node WHERE uid = ' . $this->uid . ' ORDER BY createTime DESC LIMIT ' . $limit);
   }

   public function getRecentComments($limit)
   {
      return $this->_db->select('SELECT c.nid, n.title, c.createTime FROM Comment AS c JOIN Node AS n ON c.nid = n.nid WHERE c.uid = ' . $this->uid . ' GROUP BY c.nid ORDER BY c.createTime DESC LIMIT ' . $limit);
   }

   public function getNewPrivMsgsCount()
   {
      return $this->_db->val('SELECT count(*) FROM PrivMsg WHERE isNew = 1 AND toUID = ' . $this->uid);
   }

   public function getPrivMsgsCount($type = 'inbox')
   {
      if ($type == 'sent')
      {
// sent box
         return $this->_db->val('SELECT count(DISTINCT topicMID) FROM PrivMsg WHERE mid = topicMID AND fromUid = ' . $this->uid);
      }
      else
      {
// inbox
         return $this->_db->val('SELECT count(DISTINCT topicMID) FROM PrivMsg WHERE toUid = ' . $this->uid);
      }
   }

   public function getPrivMsgs($type = 'inbox', $limit, $offset = 0)
   {
      if ($type == 'sent')
      {
         // sent box
         $sql_topic = 'SELECT topicMID, MAX(time) AS lastMessageTime FROM PrivMsg'
            . ' WHERE mid = topicMID AND fromUID = ' . $this->uid . ' AND isDeleted < 2' // isDeleted = FALSE and as sender
            . ' GROUP BY topicMID ORDER BY lastMessageTime DESC LIMIT ' . $limit . ' OFFSET ' . $offset;
      }
      else
      {
         // inbox
         $sql_topic = 'SELECT pm.topicMID, MAX(pm.time) AS lastMessageTime FROM PrivMsg AS pm'
            . ' WHERE (pm.toUID = ' . $this->uid . ' AND pm.isDeleted%2 = 0)'  // isDeleted = FALSE and as recipient
            . ' OR (pm.fromUID = ' . $this->uid . ' AND pm.isDeleted < 2 AND (SELECT COUNT(*) > 0 FROM PrivMsg AS tmp WHERE tmp.topicMID = pm.topicMID and tmp.toUID = ' . $this->uid . ') = 1)' // isDeleted = FALSE
            . ' GROUP BY topicMID ORDER BY lastMessageTime DESC LIMIT ' . $limit . ' OFFSET ' . $offset;
      }
      $topics = $this->_db->select($sql_topic);
      if (\sizeof($topics) > 0)
      {
         $tids = array();
         foreach ($topics as $r)
         {
            $tids[] = $r['topicMID'];
         }

         $sql_message = 'SELECT m.mid, m.topicMID, m.fromUID, m.toUID, m.body, m.time, MAX(m.time) AS lastMessageTime,'
            . ' (SELECT username FROM User WHERE uid = m.fromUID) AS fromName,'
            . ' (SELECT username FROM User WHERE uid = m.toUID) AS toName,'
            . ' (SELECT count(*) > 0 FROM PrivMsg AS tpm WHERE tpm.topicMID = m.topicMID AND tpm.isNew = 1 AND tpm.toUID = ' . $this->uid . ') AS isNew'
            . ' FROM PrivMsg AS m WHERE m.topicMID IN (' . \implode(',', $tids) . ') GROUP BY m.topicMID ORDER BY lastMessageTime DESC';

         return $this->_db->select($sql_message);
      }
      else
      {
         return array();
      }
   }

   public function getPrivMsgsSentCount()
   {
      return $this->_db->val('SELECT count(DISTINCT topicMID) FROM PrivMsg WHERE fromUid = ' . $this->uid);
   }

   public function getPrivMsgsSent($limit, $offset = 0)
   {
      $sql_topic = 'SELECT topicMID FROM PrivMsg'
         . ' WHERE fromUID = ' . $this->uid . ' AND isDeleted < 2' // isDeleted = FALSE and as sender
//. ' WHERE (fromUID = ' . $this->uid . ' AND isDeleted < 2) OR (toUID = ' . $this->uid . ' AND isDeleted%2 = 0)' // isDeleted = FALSE
         . ' GROUP BY topicMID ORDER BY time DESC LIMIT ' . $limit . ' OFFSET ' . $offset;
      $topic = $this->_db->select($sql_topic);
   }

   public function getUserStat($timestamp)
   {
      $sql = 'SELECT'
         . ' (SELECT count(*) FROM User) as userCount,'
         . ' (SELECT count(*) FROM User WHERE createTime >= ' . \strtotime(\date("m/d/Y")) . ' ) as userTodayCount,'
         . ' (SELECT username FROM User WHERE status = 1 ORDER BY uid DESC LIMIT 1) AS latestUser';
      $r = $this->_db->row($sql);

      $sql = 'SELECT s.uid, u.username FROM Session AS s LEFT JOIN User AS u ON s.uid = u.uid WHERE s.mtime > ' . $timestamp . ' OR s.sid = ' . $this->_db->str(\session_id());
      $arr = $this->_db->select($sql);

      $users = array();
      $guestCount = 0;
      if (isset($arr))
      {
         foreach ($arr AS $u)
         {
            if ($u['uid'] > 0)
               $users[] = $u['username'];
            else
               $guestCount++;
         }
      }
      $r['onlineUsers'] = \implode(', ', $users);
      $r['onlineUserCount'] = \sizeof($users);
      $r['onlineGuestCount'] = $guestCount;
      $r['onlineCount'] = $r['onlineUserCount'] + $r['onlineGuestCount'];

      return $r;
   }

}

//__END_OF_FILE__
