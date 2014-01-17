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
        return \in_array( $uid, [1] );
    }

    public function login( $username, $password )
    {
        $this->username = $username;
        $this->load( 'id,status' );
        if ( $this->exists() && $this->status == 1 )
        {
            $this->password = $this->hashPW( $password );
            $this->load( 'id' );
            if ( $this->exists() )
            {
                return TRUE;
            }
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
        $nids = [];
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
        return $this->call( 'get_user_recent_nodes(' . $this->id . ',10)' );
    }

    public function getRecentComments( $limit )
    {
        return $this->call( 'get_user_recent_comments(' . $this->id . ',10)' );
    }

    public function getPrivMsgsCount( $mailbox = 'inbox' )
    {
        if ( $mailbox == 'new' )
        {
            return \intval( \array_pop( \array_pop( $this->call( 'get_pm_count_new(' . $this->id . ')' ) ) ) );
        }
        else if( $mailbox == 'inbox' )
        {
            return \intval( \array_pop( \array_pop( $this->call( 'get_pm_count_inbox(' . $this->id . ')' ) ) ) );
        }
        else if ( $mailbox == 'sent' )
        {
            return \intval( \array_pop( \array_pop( $this->call( 'get_pm_count_sent(' . $this->id . ')' ) ) ) );
        }
        else
        {
            throw new \Exception( 'mailbox not found: ' . $mailbox );
        }
    }

    public function getPrivMsgs( $type = 'inbox', $limit, $offset = 0 )
    {
        if ( $type == 'sent' )
        {
            return $this->call('get_pm_list_sent(' . $this->id . ',' . $limit . ',' . $offset . ')' );
        }
        else
        {
            return $this->call('get_pm_list_inbox(' . $this->id . ',' . $limit . ',' . $offset . ')' );
        }
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

        $users = [];
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
