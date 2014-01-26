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
            'viewCount' => 'view_count',
            'weight' => 'weight',
            'status' => 'status'
        ];
        parent::__construct( $db, $table, $fields, $id, $properties );
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

    public function getYellowPageNodeList( $tids, $limit = FALSE, $offset = FALSE )
    {
        return $this->_db->call( 'get_tag_nodes_yp("' . $tids . '",' . $limit . ',' . $offset . ')' );
    }

    public function getYellowPageNode( $id )
    {
        $arr = $this->_db->call( 'get_yp_node(' . $id . ')' );
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

    public function getYellowPageNodeComments( $id, $limit = false, $offset = false )
    {
        return $this->call( 'get_yp_node_comments(' . $id . ', ' . $limit . ', ' . $offset . ')' );
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
        return $this->call( 'get_tag_recent_nodes("' . \implode( ',', (new Tag( Tag::FORUM_ID, NULL ) )->getLeafTIDs() ) . '", 15)' );
    }

    public function getHotForumTopics( $timestamp )
    {
        return $this->call( 'get_tag_hot_nodes("' . \implode( ',', (new Tag( Tag::FORUM_ID, NULL ) )->getLeafTIDs() ) . '", ' . $timestamp . ', 15)' );
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
    }

    public function getLatestImmigrationPosts()
    {
        return $this->call( 'get_tag_recent_nodes("15", 12)' );
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
        return \intval( $this->call( 'get_tag_node_count("' . $tids . '")' ) );
    }

    public function getNodeStat()
    {
        $today = \strtotime( \date( "m/d/Y" ) );
        $stats = \array_pop( $this->_db->call( 'get_node_stat(' . $today . ')' ) );
        return [
            'nodeCount' => $stats['node_count_total'],
            'nodeTodayCount' => $stats['node_count_recent'],
            'commentTodayCount' => $stats['comment_count_recent'],
            'postCount' => $stats['node_count_total'] + $stats['comment_count_total']
        ];
    }

    public function updateRating( $nid, $uid, $rating, $time )
    {
        $this->_db->call( 'update_node_rating(' . $nid . ',' . $uid . ',' . $rating . ',' . $time . ')' );
    }

    public function deleteRating( $nid, $uid )
    {
        $this->_db->call( 'delete_node_rating(' . $nid . ',' . $uid . ')' );
    }

}

//__END_OF_FILE__
