<?php

namespace site\api;

use site\Service;
use site\dbobject\Tag;
use site\dbobject\Node;

class TagAPI extends Service
{
    const NODES_PER_PAGE = 20;

    /**
     * get tags for a user
     * uri: /api/tag/<tid>
     *        /api/tag/<tid>?p=<pageNo>
     *        /api/tag/<tid>?p=hot      // only 1 page, 20 nodes
     *        /api/tag/<tid>?p=comment // only 1 page, 20 nodes
     */
    public function get()
    {
        if (empty($this->args) || !is_numeric($this->args[0])) {
            $this->forbidden();
        }

        $tid = (int) $this->args[0];
        if ($tid > 0) {
            $tag = new Tag($tid, 'id');

            if (!$tag->exists()) {
                $this->error("tag does not exist");
            }

            $tagRoot = $tag->getTagRoot();
            if (!array_key_exists(self::$city->ForumRootID, $tagRoot)) {
                $this->error("tag does not exist");
            }
        }

        $node = new Node();
        list($pageNo, $pageCount) = $this->getPagerInfo($node->getNodeCount($tid), self::NODES_PER_PAGE);
        $pager = Template::pager($pageNo, $pageCount, '/forum/' . $tid);

        $nodes = $node->getForumNodeList(self::$city->id, $tid, self::NODES_PER_PAGE, ($pageNo - 1) * self::NODES_PER_PAGE);
        $db = \lzx\db\DB::getInstance();
        $nodes = $db->query('select ');


        $nodes = $t->getNodeList(1, self::NODES_PER_PAGE, 0);

        $this->json($nodes);
    }
}

//__END_OF_FILE__
