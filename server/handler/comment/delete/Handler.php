<?php declare(strict_types=1);

namespace site\handler\comment\delete;

use lzx\exception\Forbidden;
use lzx\exception\Redirect;
use site\dbobject\Comment as CommentObject;
use site\dbobject\Node;
use site\dbobject\Tag;
use site\handler\comment\Comment;

class Handler extends Comment
{
    public function run(): void
    {
        $comment = new CommentObject();
        $comment->id = (int) $this->args[0];
        $comment->load('uid,nid');

        if ($this->request->uid != 1 && $this->request->uid != $comment->uid) {
            $this->logger->warn('wrong action : uid = ' . $this->request->uid);
            throw new Forbidden();
        }

        $this->getCacheEvent('NodeUpdate', $comment->nid)->trigger();

        $node = new Node($comment->nid, 'tid');
        if (in_array($node->tid, (new Tag(self::$city->tidForum, 'id'))->getLeafTIDs())) { // forum tag
            $this->getCacheEvent('ForumComment')->trigger();
            $this->getCacheEvent('ForumUpdate', $node->tid)->trigger();
        }

        if (in_array($node->tid, (new Tag(self::$city->tidYp, 'id'))->getLeafTIDs())) { // yellow page tag
            $this->getCacheEvent('YellowPageComment', $node->tid)->trigger();
        }

        $comment->delete();

        throw new Redirect($this->request->referer);
    }
}
