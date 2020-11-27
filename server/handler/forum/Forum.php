<?php

declare(strict_types=1);

namespace site\handler\forum;

use lzx\exception\NotFound;
use site\Controller;
use site\dbobject\Tag;

abstract class Forum extends Controller
{
    const NODES_PER_PAGE = 30;

    protected function getTagObj(): Tag
    {
        if ($this->args) {
            $tid = (int) $this->args[0];
        } elseif (array_key_exists('tagId', $this->request->data)) {
            $tid = (int) $this->request->data['tagId'];
        } else {
            $tid = self::$city->tidForum;
        }

        if ($tid != self::$city->tidForum) {
            if ($tid > 0) {
                $tag = new Tag($tid, 'id');
                $tag->load('id,root');

                if (!$tag->exists()) {
                    throw new NotFound();
                }

                if (self::$city->tidForum !== $tag->root) {
                    throw new NotFound();
                }
            } else {
                throw new NotFound();
            }
        } else {
            // main forum
            $tag = new Tag(self::$city->tidForum, 'id');
        }

        return $tag;
    }
}
