<?php

namespace site\handler\node;

use site\Controller;
use site\dbobject\Node as NodeObject;

abstract class Node extends Controller
{
    const COMMENTS_PER_PAGE = 10;

    protected function getNodeType()
    {
        $types = [
            self::$city->ForumRootID => 'ForumTopic',
            self::$city->YPRootID => 'YellowPage',
        ];

        $nid = (int) $this->args[0];
        if ($nid <= 0) {
            $this->pageNotFound();
        }
        array_shift($this->args);

        $nodeObj = new NodeObject();
        $tags = $nodeObj->getTags($nid);
        if (empty($tags)) {
            $this->pageNotFound();
        }

        $rootTagID = array_shift(array_keys($tags));

        if (!array_key_exists($rootTagID, $types)) {
            //$this->logger->error('wrong root tag : nid = ' . $nid);
            $this->pageNotFound();
        }

        return [$nid, $types[$rootTagID]];
    }
}
