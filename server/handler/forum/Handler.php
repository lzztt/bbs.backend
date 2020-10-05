<?php declare(strict_types=1);

namespace site\handler\forum;

use lzx\html\HtmlElement;
use site\dbobject\Node;
use site\dbobject\Tag;
use site\gen\theme\roselife\EditorBbcode;
use site\gen\theme\roselife\ForumList;
use site\gen\theme\roselife\TopicList;
use site\handler\forum\Forum;

class Handler extends Forum
{
    public function run(): void
    {
        $this->cache = $this->getPageCache();

        $tag = $this->getTagObj();
        $tagRoot = $tag->getTagRoot();
        $tagTree = $tag->getTagTree();

        $tid = $tag->id;
        $this->html
            ->setHeadTitle($tagTree[$tid]['name'])
            ->setHeadDescription($tagTree[$tid]['name']);

        !empty($tagTree[$tid]['children']) ? $this->showForumList($tid, $tagRoot, $tagTree) : $this->showTopicList($tid, $tagRoot);
    }

    // $forum, $groups, $boards are arrays of category id
    public function showForumList(int $tid, array $tagRoot, array $tagTree): void
    {
        $nodeInfo = [];
        $groupTrees = [];
        if ($tid == self::$city->tidForum) {
            foreach ($tagTree[$tid]['children'] as $group_id) {
                $groupTrees[$group_id] = [];
                $group = $tagTree[$group_id];
                $groupTrees[$group_id][$group_id] = $group;
                foreach ($group['children'] as $board_id) {
                    $groupTrees[$group_id][$board_id] = $tagTree[$board_id];
                    $nodeInfo[$board_id] = $this->nodeInfo($board_id);
                    $this->cache->addParent('/forum/' . $board_id);
                }
            }
        } else {
            $group_id = $tid;
            $groupTrees[$group_id] = [];
            $group = $tagTree[$group_id];
            $groupTrees[$group_id][$group_id] = $group;
            foreach ($group['children'] as $board_id) {
                $groupTrees[$group_id][$board_id] = $tagTree[$board_id];
                $nodeInfo[$board_id] = $this->nodeInfo($board_id);
                $this->cache->addParent('/forum/' . $board_id);
            }
        }

        $this->html->setContent(
            (new ForumList())
                ->setCity(self::$city->id)
                ->setGroups($groupTrees)
                ->setNodeInfo($nodeInfo)
        );
    }

    public function showTopicList(int $tid, array $tagRoot): void
    {
        $this->getCacheEvent('ForumUpdate', $tid)->addListener($this->cache);

        $node = new Node();
        list($pageNo, $pageCount) = $this->getPagerInfo($node->getNodeCount((string) $tid), self::NODES_PER_PAGE);
        $pager = HtmlElement::pager($pageNo, $pageCount, '/forum/' . $tid);

        $nodes = $node->getForumNodeList(self::$city->id, $tid, self::NODES_PER_PAGE, ($pageNo - 1) * self::NODES_PER_PAGE);
        $nids = array_column($nodes, 'id');
        foreach ($nodes as $i => $n) {
            $nodes[$i]['create_time'] = date('m/d/Y H:i', (int) $n['create_time']);
            $nodes[$i]['comment_time'] = date('m/d/Y H:i', (int) $n['comment_time']);
        }

        $editor = (new EditorBbcode())
            ->setFormHandler('/forum/' . $tid . '/node')
            ->setDisplayTitle(true)
            ->setHasFile(true)
            ->setTitle('');

        // will not build node-forum map, would be too many nodes point to forum, too big map
        $topics = (new TopicList())
            ->setTid($tid)
            ->setPager($pager)
            ->setNodes($nodes)
            ->setEditor($editor)
            ->setAjaxUri('/api/viewcount/' . implode(',', $nids));

        $this->html->setContent($topics);
    }

    protected function nodeInfo(int $tid): array
    {
        $tag = new Tag($tid, 'id');

        foreach ($tag->getNodeInfo((string) $tid) as $v) {
            $v['create_time'] = date('m/d/Y H:i', (int) $v['create_time']);
            if ($v['cid'] == 0) {
                $node = $v;
            } else {
                $comment = $v;
            }
        }
        return ['node' => $node, 'comment' => $comment];
    }
}
