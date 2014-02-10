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
        if ( $this->id > 1 )
        {
            $this->call( 'delete_user(' . $this->id . ')' );
        }
    }

    public function getAllNodeIDs()
    {
        $nids = [];
        if ( $this->id > 1 )
        {
            foreach ( $this->call( 'get_user_node_ids(' . $this->id . ')' ) as $n )
            {
                $nids[] = $n['nid'];
            }
        }
        return $nids;
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
        else if ( $mailbox == 'inbox' )
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
            return $this->call( 'get_pm_list_sent(' . $this->id . ',' . $limit . ',' . $offset . ')' );
        }
        else
        {
            return $this->call( 'get_pm_list_inbox(' . $this->id . ',' . $limit . ',' . $offset . ')' );
        }
    }

    public function validatePost( $ip, $timestamp )
    {
        // CHECK USER
        if ( $this->status != 1 )
        {
            throw new \Exception( 'This user account cannot post message.' );
        }
        $days = \intval( ($timestamp - $this->createTime) / 86400 );
        // registered less than 10 days
        if ( $days < 10 )
        {
            $geo = \geoip_record_by_name( \long2ip( $ip ) );
            // not from Texas
            if ( !$geo || $geo['region'] != 'TX' )
            {
                $oneday = \intval( $timestamp - 86400 );
                $count = \array_pop( \array_pop( $this->call( 'get_user_post_count(' . $this->id . ',' . $oneday . ')' ) ) );
                if ( $count >= $days )
                {
                    throw new \Exception( 'Quota limitation reached!<br />Your account is ' . $days . ' days old, so you can only post ' . $days . ' messages within 24 hours. <br /> You already have ' . $count . ' message posted in last 24 hours. Please wait for several hours to get more quota.' );
                }
            }
        }
    }

    public function getUserStat( $timestamp )
    {
        $stats = \array_pop( $this->call( 'get_user_stat(' . \strtotime( \date( "m/d/Y" ) ) . ')' ) );

        $onlines = $this->call( 'get_user_online(' . $timestamp . ')' );

        $users = [];
        $guestCount = 0;
        if ( isset( $onlines ) )
        {
            foreach ( $onlines AS $u )
            {
                if ( $u['uid'] > 0 )
                {
                    $users[] = $u['username'];
                }
                else
                {
                    $guestCount++;
                }
            }
        }

        return [
            'userCount' => $stats['user_count_total'],
            'userTodayCount' => $stats['user_count_recent'],
            'latestUser' => $stats['latest_user'],
            'onlineUsers' => \implode( ', ', $users ),
            'onlineUserCount' => \sizeof( $users ),
            'onlineGuestCount' => $guestCount,
            'onlineCount' => \sizeof( $users ) + $guestCount
        ];
    }

}

//__END_OF_FILE__
