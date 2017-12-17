<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;
use site\dbobject\Tag;

class Node extends DBObject
{
    public $id;
    public $uid;
    public $tid;
    public $createTime;
    public $lastModifiedTime;
    public $title;
    public $viewCount;
    public $weight;
    public $status;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'nodes';
        parent::__construct($db, $table, $id, $properties);
    }

    public function getForumNodeList($cid, $tid, $limit = 25, $offset = 0): array
    {
        return $this->call('get_tag_nodes_forum(' . $cid . ', ' . $tid . ', ' . $limit . ', ' . $offset . ')');
    }

    public function getForumNode($id, $useNewVersion = false): array
    {
        $sp = $useNewVersion ? 'get_forum_node_2' : 'get_forum_node';
        $arr = $this->call($sp . '(' . $id . ')');

        if (sizeof($arr) > 0) {
            $node = $arr[0];
            $node['files'] = $this->call('get_node_images(' . $id . ')');
            return $node;
        } else {
            return [];
        }
    }

    public function getForumNodeComments($id, $limit, $offset, $useNewVersion = false): array
    {
        $sp = $useNewVersion ? 'get_forum_node_comments_2' : 'get_forum_node_comments';
        $arr = $this->call($sp . '(' . $id . ', ' . $limit . ', ' . $offset . ')');

        foreach ($arr as $i => $r) {
            $arr[$i]['files'] = $this->call('get_comment_images(' . $r['id'] . ')');
        }

        return $arr;
    }

    public function getYellowPageNodeList($tids, $limit = false, $offset = false): array
    {
        return $this->call('get_tag_nodes_yp("' . $tids . '",' . $limit . ',' . $offset . ')');
    }

    public function getYellowPageNode($id): array
    {
        $arr = $this->call('get_yp_node(' . $id . ')');
        if (sizeof($arr) > 0) {
            return $arr[0];
        } else {
            return [];
        }
    }

    public function getYellowPageNodeComments($id, $limit = false, $offset = false): array
    {
        $arr = $this->call('get_yp_node_comments(' . $id . ', ' . $limit . ', ' . $offset . ')');

        if ($offset == 0) {
            $arr[0]['files'] = $this->call('get_comment_images(' . $arr[0]['id'] . ')');
        }

        return $arr;
    }

    public function getViewCounts($nids): array
    {
        return $this->call('get_node_view_count("' . implode(',', $nids) . '")');
    }

    public function getTags($nid): array
    {
        static $tags = [];

        if (!array_key_exists($nid, $tags)) {
            $node = new Node($nid, 'tid');
            if ($node->exists()) {
                $tag = new Tag($node->tid, null);
                $tags[$nid] = $tag->getTagRoot();
            } else {
                $tags[$nid] = [];
            }
        }
        return $tags[$nid];
    }

    public function getLatestForumTopics($forumRootID, $count): array
    {
        return $this->call('get_tag_recent_nodes("' . implode(',', (new Tag($forumRootID, null))->getLeafTIDs()) . '", ' . $count . ')');
    }

    public function getHotForumTopics($forumRootID, $count, $timestamp): array
    {
        return $this->call('get_tag_hot_nodes("' . implode(',', (new Tag($forumRootID, null))->getLeafTIDs()) . '", ' . $timestamp . ', ' . $count . ')');
    }

    public function getHotForumTopicNIDs($forumRootID, $count, $timestamp): array
    {
        return array_column($this->getHotForumTopics($forumRootID, $count, $timestamp), 'nid');
    }

    public function getLatestYellowPages($ypRootID, $count): array
    {
        return $this->call('get_tag_recent_nodes_yp("' . implode(',', (new Tag($ypRootID, null))->getLeafTIDs()) . '", ' . $count . ')');
    }

    public function getLatestForumTopicReplies($forumRootID, $count): array
    {
        return $this->call('get_tag_recent_comments("' . implode(',', (new Tag($forumRootID, null))->getLeafTIDs()) . '", ' . $count . ')');
    }

    public function getLatestYellowPageReplies($ypRootID, $count): array
    {
        return $this->call('get_tag_recent_comments_yp("' . implode(',', (new Tag($ypRootID, null))->getLeafTIDs()) . '", ' . $count . ')');
    }

    public function getNodeCount($tids): int
    {
        return intval(array_pop(array_pop($this->call('get_tag_node_count("' . $tids . '")'))));
    }

    public function getNodeStat($forumRootID): array
    {
        $today = strtotime(date("m/d/Y"));
        $stats = array_pop($this->call('get_node_stat("' . implode(',', (new Tag($forumRootID, null))->getLeafTIDs()) . '", ' . $today . ')'));
        return [
            'nodeCount'            => $stats['node_count_total'],
            'nodeTodayCount'     => $stats['node_count_recent'],
            'commentTodayCount' => $stats['comment_count_recent'],
            'postCount'            => $stats['node_count_total'] + $stats['comment_count_total']
        ];
    }
}
