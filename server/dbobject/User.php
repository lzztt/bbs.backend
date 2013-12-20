<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $username
 * @property $password
 * @property $email
 * @property $msn
 * @property $qq
 * @property $website
 * @property $firstname
 * @property $lastname
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
 */
class User extends DBObject
{

   public function __construct( $id = null, $properties = '' )
   {
      $db = DB::getInstance();
      $table = 'users';
      $fields = [
         'id' => 'id',
         'username' => 'username',
         'password' => 'password',
         'email' => 'email',
         'msn' => 'msn',
         'qq' => 'qq',
         'website' => 'website',
         'firstname' => 'firstname',
         'lastname' => 'lastname',
         'sex' => 'sex',
         'birthday' => 'birthday',
         'location' => 'location',
         'occupation' => 'occupation',
         'interests' => 'interests',
         'favoriteQuotation' => 'favorite_quotation',
         'relationship' => 'relationship',
         'signature' => 'signature',
         'createTime' => 'create_time',
         'lastAccessTime' => 'last_access_time',
         'lastAccessIP' => 'last_access_ip',
         'status' => 'status',
         'timezone' => 'timezone',
         'avatar' => 'avatar',
         'type' => 'type',
         'role' => 'role',
         'badge' => 'badge',
         'points' => 'points'
      ];
      parent::__construct( $db, $table, $fields, $id, $properties );
   }

   public function hashPW( $password )
   {
      return \md5( 'Alex' . $password . 'Tian' );
   }

   public function randomPW()
   {
      $chars = 'aABCdEeFfGHiKLMmNPRrSTWXY23456789@#$=';
      $salt = \substr( \str_shuffle( $chars ), 0, 3 );
      return $salt . \substr( \str_shuffle( $chars ), 0, 7 ); // will send generated password to email
   }

   public function isSuperUser( $uid, $cid )
   {
      return in_array( $uid, array(
         1
            ) );
   }

   public function login( $username, $password )
   {
      $this->username = $username;
      $this->load( 'id,status' );
      if ( $this->exists() && $this->status == 1 )
      {
         $hash = $this->hashPW( $password );
         $this->password = $hash;
         $this->load( 'id' );
         if ( $this->exists() )
         {
            return TRUE;
         }

         $this->password = \md5( $password );
         $this->load( 'id' );
         if ( $this->exists() )
         {
            $sql = 'UPDATE ' . $this->_table
                  . ' SET phpass = NULL, password = ' . $this->_db->str( $hash )
                  . ' WHERE id = ' . $this->id;
            $this->_db->query( $sql );
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
      $this->update( 'status' );
      $this->_db->query( 'DELETE FROM sessions WHERE uid = ' . $this->id );
      $this->_db->query( 'UPDATE nodes SET status = 0 WHERE uid = ' . $this->id );
      $this->_db->query( 'INSERT INTO spammers (email, ipInt, time) SELECT email, lastAccessIPInt, UNIX_TIMESTAMP() FROM users WHERE uid = ' . $this->id );
      //$this->_db->query('DELETE FROM comments WHERE uid = ' . $this->id);
   }

   public function getAllNodeIDs()
   {
      $nids = array( );
      if ( $this->id > 1 )
      {
         foreach ( $this->_db->select( 'SELECT nid FROM nodes WHERE uid = ' . $this->id ) as $n )
         {
            $nids[] = $n['nid'];
         }
      }
      return $nids;
   }

   public function checkSpamEmail( $email )
   {
      $count = $this->_db->val( 'SELECT COUNT(*) FROM spammers WHERE email = ' . $this->_db->str( $email ) );
      return ($count > 0 ? FALSE : TRUE);
   }

   public function getRecentNodes( $limit )
   {
      return $this->_db->select( 'SELECT nid, title, createTime FROM nodes WHERE uid = ' . $this->id . ' ORDER BY createTime DESC LIMIT ' . $limit );
   }

   public function getRecentComments( $limit )
   {
      return $this->_db->select( 'SELECT c.nid, n.title, c.createTime FROM comments AS c JOIN nodes AS n ON c.nid = n.nid WHERE c.uid = ' . $this->id . ' GROUP BY c.nid ORDER BY c.createTime DESC LIMIT ' . $limit );
   }

   public function getNewPrivMsgsCount()
   {
      return $this->_db->val( 'SELECT count(*) FROM priv_msgs WHERE is_new = 1 AND to_uid = ' . $this->id );
   }

   public function getPrivMsgsCount( $type = 'inbox' )
   {
      if ( $type == 'sent' )
      {
// sent box
         return $this->_db->val( 'SELECT count(DISTINCT msg_id) FROM priv_msgs WHERE id = msg_id AND fromUid = ' . $this->id );
      }
      else
      {
// inbox
         return $this->_db->val( 'SELECT count(DISTINCT msg_id) FROM priv_msgs WHERE to_uid = ' . $this->id );
      }
   }

   public function getPrivMsgs( $type = 'inbox', $limit, $offset = 0 )
   {
      if ( $type == 'sent' )
      {
         // sent box
         $sql_topic = 'SELECT msg_id, MAX(time) AS lastMessageTime FROM priv_msgs'
               . ' WHERE id = msg_id AND from_uid = ' . $this->id . ' AND is_deleted < 2' // is_deleted = FALSE and as sender
               . ' GROUP BY msg_id ORDER BY lastMessageTime DESC LIMIT ' . $limit . ' OFFSET ' . $offset;
      }
      else
      {
         // inbox
         $sql_topic = 'SELECT pm.msg_id, MAX(pm.time) AS lastMessageTime FROM priv_msgs AS pm'
               . ' WHERE (pm.to_uid = ' . $this->id . ' AND pm.is_deleted%2 = 0)'  // is_deleted = FALSE and as recipient
               . ' OR (pm.from_uid = ' . $this->id . ' AND pm.is_deleted < 2 AND (SELECT COUNT(*) > 0 FROM priv_msgs AS tmp WHERE tmp.msg_id = pm.msg_id and tmp.to_uid = ' . $this->id . ') = 1)' // is_deleted = FALSE
               . ' GROUP BY msg_id ORDER BY lastMessageTime DESC LIMIT ' . $limit . ' OFFSET ' . $offset;
      }
      $topics = $this->_db->select( $sql_topic );
      if ( \sizeof( $topics ) > 0 )
      {
         $tids = array( );
         foreach ( $topics as $r )
         {
            $tids[] = $r['msg_id'];
         }

         $sql_message = 'SELECT m.id, m.msg_id, m.from_uid, m.to_uid, m.body, m.time, MAX(m.time) AS lastMessageTime,'
               . ' (SELECT username FROM users WHERE id = m.from_uid) AS fromName,'
               . ' (SELECT username FROM users WHERE id = m.to_uid) AS toName,'
               . ' (SELECT count(*) > 0 FROM priv_msgs AS tpm WHERE tpm.msg_id = m.msg_id AND tpm.is_new = 1 AND tpm.to_uid = ' . $this->id . ') AS isNew'
               . ' FROM priv_msgs AS m WHERE m.msg_id IN (' . \implode( ',', $tids ) . ') GROUP BY m.msg_id ORDER BY lastMessageTime DESC';

         return $this->_db->select( $sql_message );
      }
      else
      {
         return array( );
      }
   }

   public function getPrivMsgsSentCount()
   {
      return $this->_db->val( 'SELECT count(DISTINCT msg_id) FROM priv_msgs WHERE fromUid = ' . $this->id );
   }

   public function getPrivMsgsSent( $limit, $offset = 0 )
   {
      $sql_topic = 'SELECT msg_id FROM priv_msgs'
            . ' WHERE from_uid = ' . $this->id . ' AND is_deleted < 2' // is_deleted = FALSE and as sender
//. ' WHERE (from_uid = ' . $this->id . ' AND is_deleted < 2) OR (to_uid = ' . $this->id . ' AND is_deleted%2 = 0)' // is_deleted = FALSE
            . ' GROUP BY msg_id ORDER BY time DESC LIMIT ' . $limit . ' OFFSET ' . $offset;
      $topic = $this->_db->select( $sql_topic );
   }

   public function getUserStat( $timestamp )
   {
      $sql = 'SELECT'
            . ' (SELECT count(*) FROM users) as userCount,'
            . ' (SELECT count(*) FROM users WHERE create_time >= ' . \strtotime( \date( "m/d/Y" ) ) . ' ) as userTodayCount,'
            . ' (SELECT username FROM users WHERE status = 1 ORDER BY id DESC LIMIT 1) AS latestUser';
      $r = $this->_db->row( $sql );

      $sql = 'SELECT s.uid, u.username FROM sessions AS s LEFT JOIN users AS u ON s.uid = u.id WHERE s.mtime > ' . $timestamp . ' OR s.id = ' . $this->_db->str( \session_id() );
      $arr = $this->_db->select( $sql );

      $users = array( );
      $guestCount = 0;
      if ( isset( $arr ) )
      {
         foreach ( $arr AS $u )
         {
            if ( $u['uid'] > 0 )
               $users[] = $u['username'];
            else
               $guestCount++;
         }
      }
      $r['onlineUsers'] = \implode( ', ', $users );
      $r['onlineUserCount'] = \sizeof( $users );
      $r['onlineGuestCount'] = $guestCount;
      $r['onlineCount'] = $r['onlineUserCount'] + $r['onlineGuestCount'];

      return $r;
   }

}

//__END_OF_FILE__
