<?php

declare(strict_types=1);

namespace site\handler\forum;

use lzx\exception\NotFound;
use lzx\html\HtmlElement;
use site\dbobject\Node;
use site\gen\theme\roselife\TopicList;
use site\handler\forum\Forum;

class Handler extends Forum
{
    public function run(): void
    {
        $this->cache = $this->getPageCache();

        $tag = $this->getTagObj();
        $tagTree = $tag->getTagTree();

        $tid = $tag->id;
        $this->html
            ->setHeadTitle($tagTree[$tid]['name'])
            ->setHeadDescription($tagTree[$tid]['name']);

        !empty($tagTree[$tid]['children']) ? $this->showForumList() : $this->showTopicList($tid);
    }

    // $forum, $groups, $boards are arrays of category id
    public function showForumList(): void
    {
        throw new NotFound();
    }

    public function showTopicList(int $tid): void
    {
        $this->getCacheEvent('ForumUpdate', $tid)->addListener($this->cache);

        $node = new Node();
        list($pageNo, $pageCount) = $this->getPagerInfo($node->getNodeCount((string) $tid), self::NODES_PER_PAGE);
        $pager = HtmlElement::pager($pageNo, $pageCount, '/forum/' . $tid);

        $nodes = $node->getForumNodeList(self::$city->id, $tid, self::NODES_PER_PAGE, ($pageNo - 1) * self::NODES_PER_PAGE);

        // will not build node-forum map, would be too many nodes point to forum, too big map
        $topics = (new TopicList())
            ->setTid($tid)
            ->setPager($pager)
            ->setNodes($nodes);

        $this->html->setContent($topics);
    }
}
