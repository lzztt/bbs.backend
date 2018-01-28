<?php declare(strict_types=1);

namespace site\handler\node;

use lzx\exception\NotFound;
use site\Controller;
use site\dbobject\Node as NodeObject;

abstract class Node extends Controller
{
    const FORUM_TOPIC = 0;
    const YELLOW_PAGE = 1;
    const COMMENTS_PER_PAGE = 10;

    protected function getNodeType(): array
    {
        $types = [
            self::$city->tidForum => self::FORUM_TOPIC,
            self::$city->tidYp => self::YELLOW_PAGE,
        ];

        $nid = (int) $this->args[0];
        if ($nid <= 0) {
            throw new NotFound();
        }
        array_shift($this->args);

        $nodeObj = new NodeObject();
        $tags = $nodeObj->getTags($nid);
        if (!$tags) {
            throw new NotFound();
        }

        $rootTagID = array_shift(array_keys($tags));

        if (!array_key_exists($rootTagID, $types)) {
            throw new NotFound();
        }

        return [$nid, $types[$rootTagID]];
    }
}
