<?php

namespace site\controller\comment;

use site\controller\Comment;
use site\dbobject\Tag;
use site\dbobject\Comment as CommentObject;
use site\dbobject\Node;
use site\dbobject\Image;
use site\dbobject\User;

class EditCtrler extends Comment
{

   public function run()
   {
      if ( $this->request->uid == 0 )
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->request->pageForbidden();
      }
      $action = $this->args[2];
      $this->run( $action );
   }

   public function edit()
   { // edit existing comment
      $cid = \intval( $this->args[1] );

      if ( \strlen( $this->request->post['body'] ) < 5 )
      {
         $this->error( 'Comment body is too short.' );
      }

      $comment = new CommentObject( $cid, 'nid,uid' );
      if ( $this->request->uid != 1 && $this->request->uid != $comment->uid )
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->request->pageForbidden();
      }
      $comment->body = $this->request->post['body'];
      $comment->lastModifiedTime = $this->request->timestamp;
      try
      {
         $comment->update();
      }
      catch ( \Exception $e )
      {
         $this->error( $e->getMessage(), TRUE );
      }

      // FORUM comments images
      if ( $this->request->post['update_file'] )
      {
         $files = \is_array( $this->request->post['files'] ) ? $this->request->post['files'] : [];
         $file = new Image();
         $file->updateFileList( $files, $this->config->path['file'], $comment->nid, $cid );
         $this->cache->delete( 'imageSlider' );
      }

      // YP comments
      if ( isset( $this->request->post['star'] ) && \is_numeric( $this->request->post['star'] ) )
      {
         $rating = (int) $this->request->post['star'];
         if ( $rating > 0 )
         {
            $node = new Node();
            $node->updateRating( $comment->nid, $comment->uid, $rating, $this->request->timestamp );
         }
      }

      $this->cache->delete( '/node/' . $comment->nid );

      $this->request->redirect( $this->request->referer );
   }

   public function delete()
   {
      $comment = new CommentObject();
      $comment->id = \intval( $this->args[1] );
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