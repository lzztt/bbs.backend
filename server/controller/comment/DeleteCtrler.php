<?php

namespace site\controller\comment;

use site\controller\Comment;
use site\dbobject\Tag;
use site\dbobject\Comment as CommentObject;
use site\dbobject\Node;
use site\dbobject\User;

class DeleteCtrler extends Comment
{

   public function run()
   {
      $comment = new CommentObject();
      $comment->id = $this->id;
      $comment->load( 'uid,nid' );

      if ( $this->request->uid != 1 && $this->request->uid != $comment->uid )
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->pageForbidden();
      }

      $node = new Node( $comment->nid, 'tid' );
      $this->_getIndependentCache( '/node/' . $node->id )->delete();

      if ( \in_array( $node->tid, ( new Tag( Tag::FORUM_ID, NULL ) )->getLeafTIDs() ) ) // forum tag
      {
         $this->_getIndependentCache( '/forum/' . $node->tid )->delete();
      }

      if ( \in_array( $node->tid, ( new Tag( Tag::YP_ID, NULL ) )->getLeafTIDs() ) ) // yellow page tag
      {
         $c = new CommentObject();
         $c->nid = $comment->nid;
         $c->uid = $comment->uid;
         if ( $c->getCount() == 0 )
         {
            $node = new Node();
            $node->deleteRating( $comment->nid, $comment->uid );
         }
         $this->_getIndependentCache( 'latestYellowPageReplies' )->delete();
      }

      $user = new User( $comment->uid, 'points' );
      $user->points -= 1;
      $user->update( 'points' );

      $comment->delete();

      $this->pageRedirect( $this->request->referer );
   }

}

//__END_OF_FILE__