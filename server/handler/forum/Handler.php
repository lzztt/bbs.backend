<?php declare(strict_types=1);

namespace site\handler\forum;

use lzx\html\Template;
use site\dbobject\Node;
use site\dbobject\Tag;
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
        $this->var['head_title'] = $tagTree[$tid]['name'];
        $this->var['head_description'] = $tagTree[$tid]['name'];

        !empty($tagTree[$tid]['children']) ? $this->showForumList($tid, $tagRoot, $tagTree) : $this->showTopicList($tid, $tagRoot);
    }

    // $forum, $groups, $boards are arrays of category id
    public function showForumList(int $tid, array $tagRoot, array $tagTree): void
    {
        $breadcrumb = [];
        foreach ($tagRoot as $i => $t) {
            $breadcrumb[$t['name']] = ($i === self::$city->tidForum ? '/forum' : ('/forum/' . $i));
        }

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
        $contents = ['groups' => $groupTrees, 'nodeInfo' => $nodeInfo];
        if (sizeof($breadcrumb) > 1) {
            $contents['breadcrumb'] = Template::breadcrumb($breadcrumb);
        }
        $this->var['content'] = new Template('forum_list', $contents);
    }

    public function showTopicList(int $tid, array $tagRoot): void
    {
        $this->getCacheEvent('ForumUpdate', $tid)->addListener($this->cache);

        $breadcrumb = [];
        foreach ($tagRoot as $i => $t) {
            $breadcrumb[$t['name']] = ($i === self::$city->tidForum ? '/forum' : ('/forum/' . $i));
        }

        $node = new Node();
        list($pageNo, $pageCount) = $this->getPagerInfo($node->getNodeCount((string) $tid), self::NODES_PER_PAGE);
        $pager = Template::pager($pageNo, $pageCount, '/forum/' . $tid);

        $nodes = $node->getForumNodeList(self::$city->id, $tid, self::NODES_PER_PAGE, ($pageNo - 1) * self::NODES_PER_PAGE);
        $nids = array_column($nodes, 'id');
        foreach ($nodes as $i => $n) {
            $nodes[$i]['create_time'] = date('m/d/Y H:i', (int) $n['create_time']);
            $nodes[$i]['comment_time'] = date('m/d/Y H:i', (int) $n['comment_time']);
        }

        $editor_contents = [
            'form_handler' => '/forum/' . $tid . '/node',
            'displayTitle' => true,
            'hasFile' => true
        ];
        $editor = new Template('editor_bbcode', $editor_contents);

        // will not build node-forum map, would be too many nodes point to forum, too big map

        $contents = [
            'tid' => $tid,
            'boardDescription' => $tagRoot[$tid]['description'],
            'breadcrumb' => Template::breadcrumb($breadcrumb),
            'pager' => $pager,
            'nodes' => ($nodes ? $nodes : null),
            'editor' => $editor,
            'ajaxURI' => '/api/viewcount/' . implode(',', $nids)
        ];
        $this->var['content'] = new Template('topic_list', $contents);
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
