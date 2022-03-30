<?php

declare(strict_types=1);

namespace site\handler\node;

use lzx\exception\NotFound;
use site\Controller;

abstract class Node extends Controller
{
    const FORUM_TOPIC = 0;
    const YELLOW_PAGE = 1;
    const COMMENTS_PER_PAGE = 10;

    protected function getNodeId(): int
    {
        if (count($this->args) < 1) {
            throw new NotFound();
        }

        $nid = (int) $this->args[0];
        if ($nid <= 0) {
            throw new NotFound();
        }
        array_shift($this->args);
        return $nid;
    }
}
