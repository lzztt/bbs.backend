<?php

namespace site\handler\comment\delete;

use site\handler\comment\Comment;
use site\dbobject\Tag;
use site\dbobject\Comment as CommentObject;
use site\dbobject\Node;
use site\dbobject\User;

class Handler extends Comment
{
    public function run()
    {
        $comment = new CommentObject();
        $comment->id = $this->id;
        $comment->load('uid,nid');

        if ($this->request->uid != 1 && $this->request->uid != $comment->uid) {
            $this->logger->warn('wrong action : uid = ' . $this->request->uid);
            $this->pageForbidden();
        }

        $this->getCacheEvent('NodeUpdate', $comment->nid)->trigger();

        $node = new Node($comment->nid, 'tid');
        if (in_array($node->tid, ( new Tag(self::$city->ForumRootID, null) )->getLeafTIDs())) { // forum tag
            $this->getCacheEvent('ForumComment')->trigger();
            $this->getCacheEvent('ForumUpdate', $node->tid)->trigger();
        }

        if (in_array($node->tid, ( new Tag(self::$city->YPRootID, null) )->getLeafTIDs())) { // yellow page tag
            $this->getCacheEvent('YellowPageComment', $node->tid)->trigger();
            /*
              $c = new CommentObject();
              $c->nid = $comment->nid;
              $c->uid = $comment->uid;
              if ( $c->getCount() == 0 )
              {
              $node = new Node();
              $node->deleteRating( $comment->nid, $comment->uid );
              } */
        }

        /*
        $user = new User( $comment->uid, 'points' );
        $user->points -= 1;
        $user->update( 'points' );
         */

        $comment->delete();

        $this->pageRedirect($this->request->referer);
    }
}

//__END_OF_FILE__
