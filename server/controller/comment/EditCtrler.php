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
      // edit existing comment
      $cid = (int) $this->args[ 0 ];

      if ( \strlen( $this->request->post[ 'body' ] ) < 5 )
      {
         $this->error( 'Comment body is too short.' );
      }

      $comment = new CommentObject( $cid, 'nid,uid' );
      if ( $this->request->uid != 1 && $this->request->uid != $comment->uid )
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->request->pageForbidden();
      }
      $comment->body = $this->request->post[ 'body' ];
      $comment->lastModifiedTime = $this->request->timestamp;
      try
      {
         $comment->update( 'body,lastModifiedTime' );
      }
      catch (\Exception $e)
      {
         $this->error( $e->getMessage(), TRUE );
      }

      // FORUM comments images
      if ( $this->request->post[ 'update_file' ] )
      {
         $files = \is_array( $this->request->post[ 'files' ] ) ? $this->request->post[ 'files' ] : [ ];
         $file = new Image();
         $file->updateFileList( $files, $this->config->path[ 'file' ], $comment->nid, $cid );
         $this->cache->delete( 'imageSlider' );
      }

      // YP comments
      if ( isset( $this->request->post[ 'star' ] ) && \is_numeric( $this->request->post[ 'star' ] ) )
      {
         $rating = (int) $this->request->post[ 'star' ];
         if ( $rating > 0 )
         {
            $node = new Node();
            $node->updateRating( $comment->nid, $comment->uid, $rating, $this->request->timestamp );
         }
      }

      $this->cache->delete( '/node/' . $comment->nid );

      $this->request->redirect( $this->request->referer );
   }

}

//__END_OF_FILE__