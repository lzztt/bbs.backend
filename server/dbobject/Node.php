<?php

declare(strict_types=1);

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
    public $lastCommentTime;
    public $title;
    public $viewCount;
    public $reputation;
    public $status;

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'nodes', $id, $properties);
    }

    public function getForumNodeList(int $cid, int $tid, int $limit = 25, int $offset = 0): array
    {
        return $this->call('get_tag_nodes_forum(' . $cid . ', ' . $tid . ', ' . $limit . ', ' . $offset . ')');
    }

    public function getForumNode(int $id, bool $useNewVersion = false): array
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

    public function getForumNodeComments(int $id, int $limit, int $offset, bool $useNewVersion = false): array
    {
        $sp = $useNewVersion ? 'get_forum_node_comments_2' : 'get_forum_node_comments';
        $arr = $this->call($sp . '(' . $id . ', ' . $limit . ', ' . $offset . ')');

        foreach ($arr as $i => $r) {
            $arr[$i]['files'] = $this->call('get_comment_images(' . $r['id'] . ')');
        }

        return $arr;
    }

    public function getYellowPageNodeList(string $tids, int $limit, int $offset): array
    {
        return $this->call('get_tag_nodes_yp("' . $tids . '",' . $limit . ',' . $offset . ')');
    }

    public function getYellowPageNode(int $id): array
    {
        $arr = $this->call('get_yp_node(' . $id . ')');
        if (sizeof($arr) > 0) {
            return $arr[0];
        } else {
            return [];
        }
    }

    public function getYellowPageNodeComments(int $id, int $limit, int $offset): array
    {
        $arr = $this->call('get_yp_node_comments(' . $id . ', ' . $limit . ', ' . $offset . ')');

        if ($offset == 0) {
            $arr[0]['files'] = $this->call('get_comment_images(' . $arr[0]['id'] . ')');
        }

        return $arr;
    }

    public function getViewCounts(array $nids): array
    {
        return $this->call('get_node_view_count("' . implode(',', $nids) . '")');
    }

    public function getCommenterCount(int $nid): int
    {
        $sql = "
        SELECT COUNT(DISTINCT c.uid) AS count
            FROM nodes AS n JOIN comments AS c
                ON n.id = c.nid AND c.uid != n.uid
            WHERE n.id = $nid;";
        return (int) array_pop(array_pop($this->db->query($sql)));
    }

    public function getLatestForumTopics(int $forumRootID, int $count): array
    {
        return $this->call('get_tag_recent_nodes("' . implode(',', (new Tag($forumRootID, 'id'))->getLeafTIDs()) . '", ' . $count . ')');
    }

    public function getHotForumTopics(int $forumRootID, int $count, int $timestamp): array
    {
        return $this->call('get_tag_hot_nodes("' . implode(',', (new Tag($forumRootID, 'id'))->getLeafTIDs()) . '", ' . $timestamp . ', ' . $count . ')');
    }

    public function getHotForumTopicNIDs(int $forumRootID, int $count, int $timestamp): array
    {
        return array_column($this->getHotForumTopics($forumRootID, $count, $timestamp), 'nid');
    }

    public function getLatestYellowPages(int $ypRootID, int $count): array
    {
        return $this->call('get_tag_recent_nodes_yp("' . implode(',', (new Tag($ypRootID, 'id'))->getLeafTIDs()) . '", ' . $count . ')');
    }

    public function getLatestForumTopicReplies(int $forumRootID, int $count): array
    {
        return $this->call('get_tag_recent_comments("' . implode(',', (new Tag($forumRootID, 'id'))->getLeafTIDs()) . '", ' . $count . ')');
    }

    public function getLatestYellowPageReplies(int $ypRootID, int $count): array
    {
        return $this->call('get_tag_recent_comments_yp("' . implode(',', (new Tag($ypRootID, 'id'))->getLeafTIDs()) . '", ' . $count . ')');
    }

    public function getNodeCount(string $tids): int
    {
        return intval(array_pop(array_pop($this->call('get_tag_node_count("' . $tids . '")'))));
    }

    public function getNodeStat(int $forumRootID): array
    {
        $today = strtotime(date("m/d/Y"));
        $stats = array_pop($this->call('get_node_stat("' . implode(',', (new Tag($forumRootID, 'id'))->getLeafTIDs()) . '", ' . $today . ')'));
        return [
            'nodeCount' => $stats['node_count_total'],
            'nodeTodayCount' => $stats['node_count_recent'],
            'commentTodayCount' => $stats['comment_count_recent'],
            'postCount' => $stats['node_count_total'] + $stats['comment_count_total']
        ];
    }
}
