<?php

namespace site\controller\node;

use site\controller\Node;
use site\dbobject\Node as NodeObject;
use site\dbobject\Comment;
use site\dbobject\Image;
use site\dbobject\User;
use lzx\core\Mailer;

class CommentCtrler extends Node
{

   public function run()
   {
      if ( $this->request->uid == self::GUEST_UID )
      {
         $this->pageForbidden();
      }
      
      list($nid, $type) = $this->_getNodeType();
      $method = '_comment' . $type;
      $this->$method( $nid );
   }

   private function _commentForumTopic( $nid )
   {
      // create new comment
      $node = new NodeObject( $nid, 'tid,status' );

      if ( !$node->exists() || $node->status == 0 )
      {
         $this->error( 'node does not exist.' );
      }

      if ( strlen( $this->request->post[ 'body' ] ) < 5 )
      {
         $this->error( '错误：评论正文字数太少。' );
      }

      $user = new User( $this->request->uid, 'createTime,points,status' );
      try
      {
         $user->validatePost( $this->request->ip, $this->request->timestamp, $this->request->post[ 'body' ] );
         $comment = new Comment();
         $comment->nid = $nid;
         $comment->uid = $this->request->uid;
         $comment->tid = $node->tid;
         $comment->body = $this->request->post[ 'body' ];
         $comment->createTime = $this->request->timestamp;
         $comment->add();
      }
      catch ( \Exception $e )
      {
         // spammer found
         if ( $user->isSpammer() )
         {
            $this->logger->info( 'SPAMMER FOUND: uid=' . $user->id );
            $u = new User();
            $u->lastAccessIP = \ip2long( $this->request->ip );
            $users = $u->getList( 'createTime' );
            $deleteAll = TRUE;
            if ( \sizeof( $users ) > 0 )
            {
               // check if we have old users that from this ip
               foreach ( $users as $u )
               {
                  if ( $this->request->timestamp - $u[ 'createTime' ] > 2592000 )
                  {
                     $deleteAll = FALSE;
                     break;
                  }
               }

               if ( $deleteAll )
               {
                  $log = 'SPAMMER FROM IP ' . $this->request->ip . ': uid=';
                  foreach ( $users as $u )
                  {
                     $spammer = new User( $u[ 'id' ], NULL );
                     $spammer->delete();
                     $log = $log . $spammer->id . ' ';
                  }
                  $this->logger->info( $log );
               }
            }
            if ( $this->config->webmaster )
            {
               $mailer = new Mailer();
               $mailer->subject = 'SPAMMER detected and deleted (' . \sizeof( $users ) . ($deleteAll ? ' deleted)' : ' not deleted)');
               $mailer->body = ' --node-- ' . $this->request->post[ 'title' ] . \PHP_EOL . $this->request->post[ 'body' ];
               $mailer->to = $this->config->webmaster;
               $mailer->send();
            }
         }

         $this->logger->error( $e->getMessage() . \PHP_EOL . ' --comment-- ' . $this->request->post[ 'body' ] );
         $this->error( $e->getMessage() );
      }

      if ( $this->request->post[ 'files' ] )
      {
         $file = new Image();
         $file->updateFileList( $this->request->post[ 'files' ], $this->config->path[ 'file' ], $nid, $comment->id );
         $this->_getCacheEvent( 'ImageUpdate' )->trigger();
      }

      $user->points += 1;
      $user->update( 'points' );

      $this->_getCacheEvent( 'NodeUpdate', $nid )->trigger();
      $this->_getCacheEvent( 'ForumComment' )->trigger();
      $this->_getCacheEvent( 'ForumUpdate', $node->tid )->trigger();

      if ( \in_array( $nid, $node->getHotForumTopicNIDs( $this->_forumRootID[ $this->site ], 15, $this->request->timestamp - 604800 ) ) )
      {
         $this->_getIndependentCache( 'hotForumTopics' )->delete();
      }

      $this->pageRedirect( '/node/' . $nid . '?p=l#comment' . $comment->id );
   }

   private function _commentYellowPage( $nid )
   {
      // create new comment
      $node = new NodeObject( $nid, 'status' );

      if ( !$node->exists() || $node->status == 0 )
      {
         $this->error( 'node does not exist.' );
      }

      if ( strlen( $this->request->post[ 'body' ] ) < 5 )
      {
         $this->error( '错误：评论正文字数太少。' );
      }

      $user = new User( $this->request->uid, 'createTime,points,status' );
      try
      {
         $user->validatePost( $this->request->ip, $this->request->timestamp, $this->request->post[ 'body' ] );
         $comment = new Comment();
         $comment->nid = $nid;
         $comment->uid = $this->request->uid;
         $comment->body = $this->request->post[ 'body' ];
         $comment->createTime = $this->request->timestamp;
         $comment->add();
      }
      catch ( \Exception $e )
      {
         // spammer found
         if ( $user->isSpammer() )
         {
            $this->logger->info( 'SPAMMER FOUND: uid=' . $user->id );
            $u = new User();
            $u->lastAccessIP = \ip2long( $this->request->ip );
            $users = $u->getList( 'createTime' );
            $deleteAll = TRUE;
            if ( \sizeof( $users ) > 0 )
            {
               // check if we have old users that from this ip
               foreach ( $users as $u )
               {
                  if ( $this->request->timestamp - $u[ 'createTime' ] > 2592000 )
                  {
                     $deleteAll = FALSE;
                     break;
                  }
               }

               if ( $deleteAll )
               {
                  $log = 'SPAMMER FROM IP ' . $this->request->ip . ': uid=';
                  foreach ( $users as $u )
                  {
                     $spammer = new User( $u[ 'id' ], NULL );
                     $spammer->delete();
                     $log = $log . $spammer->id . ' ';
                  }
                  $this->logger->info( $log );
               }
            }
            if ( $this->config->webmaster )
            {
               $mailer = new Mailer();
               $mailer->subject = 'SPAMMER detected and deleted (' . \sizeof( $users ) . ($deleteAll ? ' deleted)' : ' not deleted)');
               $mailer->body = ' --node-- ' . $this->request->post[ 'title' ] . \PHP_EOL . $this->request->post[ 'body' ];
               $mailer->to = $this->config->webmaster;
               $mailer->send();
            }
         }

         $this->logger->error( $e->getMessage() . \PHP_EOL . ' --comment-- ' . $this->request->post[ 'body' ] );
         $this->error( $e->getMessage() );
      }

      if ( isset( $this->request->post[ 'star' ] ) && \is_numeric( $this->request->post[ 'star' ] ) )
      {
         $rating = (int) $this->request->post[ 'star' ];
         if ( $rating > 0 )
         {
            $node->updateRating( $nid, $this->request->uid, $rating, $this->request->timestamp );
         }
      }

      $user->points += 1;
      $user->update( 'points' );

      $this->_getCacheEvent( 'NodeUpdate', $nid )->trigger();
      $this->_getCacheEvent( 'YellowPageComment' )->trigger();

      $this->pageRedirect( '/node/' . $nid . '?p=l#commentomment' . $comment->id );
   }

}

//__END_OF_FILE__
