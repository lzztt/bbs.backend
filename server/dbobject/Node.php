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

    public function getForumNode(int $id): array
    {
        $sp = 'get_forum_node';
        $arr = $this->call($sp . '(' . $id . ')');

        if (sizeof($arr) > 0) {
            $node = $arr[0];
            $node['files'] = $this->call('get_node_images(' . $id . ')');
            return $node;
        } else {
            return [];
        }
    }

    public function getForumNodeComments(int $id, int $limit, int $offset): array
    {
        $sp = 'get_forum_node_comments';
        $arr = $this->call($sp . '(' . $id . ', ' . $limit . ', ' . $offset . ')');

        foreach ($arr as $i => $r) {
            $arr[$i]['files'] = $this->call('get_comment_images(' . $r['id'] . ')');
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

    public function getLatestYellowPages(int $count): array
    {
        return $this->call('get_tag_recent_nodes_yp(' . $count . ')');
    }

    public function getLatestForumTopicReplies(int $forumRootID, int $count): array
    {
        $sql = '
        SELECT id AS nid, last_comment_time AS create_time, title,
            (SELECT COUNT(*) - 1 FROM comments AS c WHERE c.nid = n.id) AS comment_count
        FROM nodes AS n
        WHERE tid IN ('  . implode(',', (new Tag($forumRootID, 'id'))->getLeafTIDs()) .  ')
            AND status = 1 AND create_time != last_comment_time
        ORDER BY last_comment_time DESC
        LIMIT ' . $count;

        return $this->db->query($sql);
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
            'postCount' => $stats['comment_count_total']
        ];
    }
}
