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
      $comment->id = (int) $this->args[ 0 ];
      $comment->load( 'uid,nid' );

      if ( $this->request->uid != 1 && $this->request->uid != $comment->uid )
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->request->pageForbidden();
      }

      $node = new Node( $comment->nid, 'tid' );
      $this->cache->delete( '/node/' . $node->id );

      if ( \in_array( $node->tid, ( new Tag( Tag::FORUM_ID, NULL ) )->getLeafTIDs() ) ) // forum tag
      {
         $this->cache->delete( '/forum/' . $node->tid );
         // take care by cache map
         //$this->cache->delete('latestForumTopicReplies');
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
         $this->cache->delete( 'latestYellowPageReplies' );
      }

      $user = new User( $comment->uid, 'points' );
      $user->points -= 1;
      $user->update( 'points' );

      $comment->delete();

      $this->request->redirect( $this->request->referer );
   }

}

//__END_OF_FILE__