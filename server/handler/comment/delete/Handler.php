<?php

namespace site\handler\comment\delete;

use site\handler\comment\Comment;
use site\dbobject\Tag;
use site\dbobject\Comment as CommentObject;
use site\dbobject\Node;

class Handler extends Comment
{
    public function run()
    {
        $comment = new CommentObject();
        $comment->id = (int) $this->args[0];
        $comment->load('uid,nid');

        if ($this->request->uid != 1 && $this->request->uid != $comment->uid) {
            $this->logger->warn('wrong action : uid = ' . $this->request->uid);
            $this->pageForbidden();
        }

        $this->getCacheEvent('NodeUpdate', $comment->nid)->trigger();

        $node = new Node($comment->nid, 'tid');
        if (in_array($node->tid, (new Tag(self::$city->ForumRootID, null))->getLeafTIDs())) { // forum tag
            $this->getCacheEvent('ForumComment')->trigger();
            $this->getCacheEvent('ForumUpdate', $node->tid)->trigger();
        }

        if (in_array($node->tid, (new Tag(self::$city->YPRootID, null))->getLeafTIDs())) { // yellow page tag
            $this->getCacheEvent('YellowPageComment', $node->tid)->trigger();
        }

        $comment->delete();

        $this->pageRedirect($this->request->referer);
    }
}
