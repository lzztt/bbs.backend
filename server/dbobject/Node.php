<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;
use site\dbobject\Tag;

/**
 * @property $id
 * @property $tid
 * @property $uid
 * @property $createTime
 * @property $lastModifiedTime
 * @property $title
 * @property $body
 * @property $hash
 * @property $viewCount
 * @property $weight
 * @property $status
 */
class Node extends DBObject
{

    public function __construct( $id = null, $properties = '' )
    {
        $db = DB::getInstance();
        $table = 'nodes';
        $fields = [
            'id' => 'id',
            'uid' => 'uid',
            'tid' => 'tid',
            'createTime' => 'create_time',
            'lastModifiedTime' => 'last_modified_time',
            'title' => 'title',
            'body' => 'body',
            'hash' => 'hash',
            'viewCount' => 'view_count',
            'weight' => 'weight',
            'status' => 'status'
        ];
        parent::__construct( $db, $table, $fields, $id, $properties );
    }

    public function getHash()
    {
        return \crc32( $this->body );
    }

    public function add()
    {
        // CHECK USER
        $userInfo = $this->_db->row( 'SELECT create_time AS createTime, last_access_ip AS lastAccessIPInt, status FROM users WHERE id = ' . $this->uid );
        if ( \intval( $userInfo['status'] ) != 1 )
        {
            throw new \Exception( 'This User account cannot post message.' );
        }
        $days = \intval( ($this->createTime - $userInfo['createTime']) / 86400 );
        // registered less than 1 month
        if ( $days < 30 )
        {
            $geo = \geoip_record_by_name( \long2ip( $userInfo['lastAccessIPInt'] ) );
            // not from Texas
            if ( !$geo || $geo['region'] != 'TX' )
            {
                //select (select count(*) from Node where uid = 126 and createTime > unix_timestamp() - 86400) + ( select count(*) from Comment where uid = 126 and createTime > unix_timestamp() - 86400) AS count
                $oneday = \intval( $this->createTime - 86400 );
                $count = $this->_db->val(
                    'SELECT 
                  ( SELECT count(*) FROM nodes WHERE uid = ' . $this->uid . ' AND create_time > ' . $oneday . ' ) +
                  ( SELECT count(*) FROM comments WHERE uid = ' . $this->uid . ' AND create_time > ' . $oneday . ' ) AS c'
                );
                if ( $count >= $days )
                {
                    throw new \Exception( 'Quota limitation reached!<br />Your account is ' . $days . ' days old, so you can only post ' . $days . ' messages within 24 hours. <br /> You already have ' . $count . ' message posted in last 24 hours. Please wait for several hours to get more quota.' );
                }
            }
        }
        parent::add();
    }

    public function update( $properties = '' )
    {
        // check spam
        // no need for update, only need for create
        // check duplicate      
        if ( $properties == '' )
        {
            $this->hash = $this->getHash();
        }
        else
        {
            $f = \explode( ',', $properties );
            if ( \in_array( 'title', $f ) || \in_array( 'body', $f ) )
            {
                $this->hash = $this->getHash();
                if ( !\in_array( 'hash', $f ) )
                {
                    $properties .=',hash';
                }
            }
        }

        if ( isset( $this->hash ) )
        {
            $count = $this->_db->val( 'SELECT count(*) FROM ' . $this->_table . ' WHERE hash = ' . $this->hash . ' AND uid = ' . $this->uid . ' AND createTime > ' . (\intval( $this->createTime ) - 86400) . ' AND id != ' . $this->id );
            if ( $count > 0 )
            {
                throw new \Exception( 'duplicate node found' );
            }
        }

        parent::update( $properties );
    }

    public function getForumNodeList( $tid, $limit = 25, $offset = 0 )
    {
        return $this->call( 'get_tag_nodes_forum(' . $tid . ', ' . $limit . ', ' . $offset . ')' );
    }

    public function getForumNode( $id )
    {
        $arr = $this->call( 'get_forum_node(' . $id . ')' );

        if ( \sizeof( $arr ) > 0 )
        {
            $arr[0]['files'] = $this->call( 'get_node_images(' . $id . ')' );
            return $arr[0];
        }
        else
        {
            return NULL;
        }
    }

    public function getForumNodeComments( $id, $limit = 10, $offset = 0 )
    {
        $arr = $this->call( 'get_forum_node_comments(' . $id . ', ' . $limit . ', ' . $offset . ')' );

        foreach ( $arr as $i => $r )
        {
            $arr[$i]['files'] = $this->call( 'get_comment_images(' . $r['id'] . ')' );
        }

        return $arr;
    }

    public function getYellowPageNodeList( $tid, $limit = FALSE, $offset = FALSE )
    {
        if ( \is_array( $tid ) )
        {
            $where = 'IN (' . \implode( ',', $tid ) . ')';
        }
        else
        {
            $where = '= ' . (int) $tid;
        }

        $limit = ($limit > 0) ? 'LIMIT ' . $limit : '';
        $offset = ($offset > 0) ? 'OFFSET ' . $offset : '';

        $sql = 'SELECT n.id, n.title, n.view_count AS viewCount, yp.*,'
            . ' (SELECT COUNT(*) FROM comments AS c WHERE c.nid = n.id) AS commentCount,'
            . ' IFNULL((SELECT c.create_time FROM comments AS c WHERE c.nid = n.id ORDER BY c.create_time DESC LIMIT 1), n.create_time) AS lastUpdateTime'
            . ' FROM nodes AS n JOIN node_yellowpages AS yp ON n.id = yp.nid'
            . ' WHERE n.status > 0 AND n.tid ' . $where
            . ' ORDER BY n.weight DESC, lastUpdateTime DESC ' . $limit . ' ' . $offset;

        $list = $this->_db->select( $sql );

        foreach ( $list as $i => $n )
        {
            $sql = 'SELECT avg(rating) AS ratingAvg, count(*) AS ratingCount FROM yp_ratings WHERE nid = ' . $n['id'];
            $list[$i] = \array_merge( $n, $this->_db->row( $sql ) );
        }

        return $list;
    }

    public function getYellowPageNode( $id )
    {
        $sql = 'SELECT n.title, n.view_count AS viewCount, n.body, yp.*, r.ratingAvg, r.ratingCount,'
            . ' (SELECT COUNT(*) FROM comments AS c WHERE c.nid = n.id) AS commentCount,'
            . ' IFNULL((SELECT c.create_time FROM comments AS c WHERE c.nid = n.id ORDER BY c.create_time DESC LIMIT 1), n.create_time) AS lastUpdateTime'
            . ' FROM nodes AS n JOIN node_yellowpages AS yp ON n.id = yp.nid,'
            . ' (SELECT avg(rating) AS ratingAvg, count(*) AS ratingCount FROM yp_ratings WHERE nid = ' . $id . ') AS r'
            . ' WHERE n.status > 0 AND n.id = ' . $id;

        $arr = $this->_db->row( $sql );

        if ( \sizeof( $arr ) > 0 )
        {
            $arr['files'] = $this->_db->select( 'SELECT id AS fid, name, path FROM images WHERE cid IS NULL AND nid = ' . $id . ' ORDER BY id ASC' );
            return $arr;
        }
        else
        {
            return NULL;
        }
    }

    public function getYellowPageNodeComments( $id, $limit = false, $offset = false )
    {
        $limit = ($limit > 0) ? 'LIMIT ' . $limit : '';
        $offset = ($offset > 0) ? 'OFFSET ' . $offset : '';

        $sql = 'SELECT c.*, u.username,'
            . ' (SELECT rating FROM yp_ratings WHERE nid = c.nid AND uid = c.uid) AS rating'
            . ' FROM comments AS c JOIN users AS u ON c.uid = u.id'
            . ' WHERE c.nid = ' . $id . ' ORDER BY c.create_time ASC ' . $limit . ' ' . $offset;
        return $this->_db->select( $sql );
    }

    /**
     * get tag tree for a node
     * @staticvar array $tags
     * @param type $id
     * @return type
     */
    public function getTags( $nid )
    {
        static $tags = [];

        if ( !\array_key_exists( $nid, $tags ) )
        {
            $node = new Node( $nid, 'tid' );
            $tag = new Tag( $node->tid, NULL );
            $tags[$nid] = $tag->getTagRoot();
        }
        return $tags[$nid];
    }

    public function getLatestForumTopics()
    {
        //$sql = 'SELECT id, title, create_time AS createTime FROM nodes WHERE tid IN (' . \implode( ',', (new Tag( Tag::FORUM_ID, NULL ) )->getLeafTIDs() ) . ') AND status = 1 ORDER BY create_time DESC LIMIT 15';
        //return $this->_db->select( $sql );
        return $this->call( 'get_tag_recent_nodes("' . \implode( ',', (new Tag( Tag::FORUM_ID, NULL ) )->getLeafTIDs() ) . '", 15)' );
    }

    public function getHotForumTopics( $timestamp )
    {
        return $this->call( 'get_tag_hot_nodes("' . \implode( ',', (new Tag( Tag::FORUM_ID, NULL ) )->getLeafTIDs() ) . '", ' . $timestamp . ', 15)' );
        //$sql = 'SELECT n.id, n.title, (SELECT count(*) FROM comments AS c WHERE c.nid = n.id) AS commentCount '
        //    . 'FROM nodes AS n WHERE n.create_time > ' . $timestamp . ' AND n.tid IN (' . \implode( ',', (new Tag( Tag::FORUM_ID, NULL ) )->getLeafTIDs() ) . ') AND n.status = 1 ORDER BY commentCount DESC LIMIT 15';
        //return $this->_db->select( $sql );
    }

    public function getHotForumTopicNIDs( $timestamp )
    {
        $ids = [];
        foreach ( $this->getHotForumTopics( $timestamp ) as $t )
        {
            $ids[] = $t['id'];
        }
        return $ids;
    }

    public function getLatestYellowPages()
    {
        return $this->call( 'get_tag_recent_nodes_yp("' . \implode( ',', (new Tag( Tag::YP_ID, NULL ) )->getLeafTIDs() ) . '", 15)' );
        //$sql = 'SELECT id, title, create_time AS createTime FROM nodes WHERE tid IN (' . \implode( ',', (new Tag( Tag::YP_ID, NULL ) )->getLeafTIDs() ) . ') AND status = 1 ORDER BY create_time DESC LIMIT 15';
        //return $this->_db->select( $sql );
    }

    public function getLatestImmigrationPosts()
    {
        return $this->call( 'get_tag_recent_nodes("15", 12)' );
        //$sql = 'SELECT id, title, create_time AS createTime FROM nodes WHERE tid = 15 AND status = 1 ORDER BY create_time DESC LIMIT 12';
        //return $this->_db->select( $sql );
    }

    public function getLatestForumTopicReplies()
    {
        return $this->call( 'get_tag_recent_comments("' . \implode( ',', (new Tag( Tag::FORUM_ID, NULL ) )->getLeafTIDs() ) . '", 15)' );
    }

    public function getLatestYellowPageReplies()
    {
        return $this->call( 'get_tag_recent_comments("' . \implode( ',', (new Tag( Tag::YP_ID, NULL ) )->getLeafTIDs() ) . '", 15)' );
    }

    public function getNodeCount( $tids )
    {
        return \intval( $this->call( 'get_tag_node_count(' . $tids . ')' ) );
    }

    public function getNodeStat()
    {
        $today = \strtotime( \date( "m/d/Y" ) );
        $sql = 'SELECT'
            . ' (SELECT count(*) FROM nodes) AS nodeCount,'
            . ' (SELECT count(*) FROM comments) AS commentCount,'
            . ' (SELECT count(*) FROM nodes WHERE status = 1 AND create_time >= ' . $today . ' ) as nodeTodayCount,'
            . ' (SELECT count(*) FROM comments WHERE create_time >= ' . $today . ' ) as commentTodayCount';
        $r = $this->_db->row( $sql );
        $r['postCount'] = $r['nodeCount'] + $r['commentCount'];
        unset( $r['commentCount'] );

        return $r;
    }

    public function updateRating( $nid, $uid, $rating, $time )
    {
        $this->_db->query( 'REPLACE INTO yp_ratings (nid,uid,rating,time) VALUES (' . $nid . ',' . $uid . ',' . $rating . ',' . $time . ')' );
    }

    public function deleteRating( $nid, $uid )
    {
        $this->_db->query( 'DELETE FROM yp_ratings WHERE nid = ' . $nid . ' AND uid = ' . $uid );
    }

    /**
     * @param \lzx\core\Request $request
     */
    public function validatePostContent( $request )
    {
        return TRUE;

        //$request->uid;
        $nodeCount = $this->_db->val( 'SELECT COUNT(*) FROM nodes WHERE uid = ' . $request->uid );
        if ( $nodeCount > 3 )
        {
            return TRUE;
        }

        $geo = \geoip_record_by_name( $request->ip );
        if ( $geo['country_code'] == 'US' )
        {
            return TRUE;
        }

        // check against previous 2 nodes
        if ( $nodeCount > 1 )
        {
            $arr = $this->_db->query( 'SELECT title, body FROM nodes WHERE uid = ' . $request->uid . 'ORDER BY id DESC LIMIT 2' );
            foreach ( $arr as $data )
            {
                // check title
                // check body

                if ( FALSE )
                {
                    return FALSE;
                }
            }
        }
        // check against previous 2 comments
        $arr = $this->_db->query( 'SELECT body FROM comments WHERE uid = ' . $request->uid . 'ORDER BY cid DESC LIMIT 2' );
        foreach ( $arr as $body )
        {
            // check body
            if ( FALSE )
            {
                return FALSE;
            }
        }

        // check against example spam posts (body only)
        $words = $this->_db->query( 'SELECT word FROM spam_words' );
        foreach ( $words as $w )
        {
            if ( FALSE )
            {
                return FALSE;
            }
        }

        return TRUE;
    }

}

//__END_OF_FILE__
