<?php

namespace site\controller\forum;

use site\controller\Forum;
use site\dbobject\Node;
use site\dbobject\Image;
use site\dbobject\User;
use lzx\core\Mailer;

class NodeCtrler extends Forum
{

   public function run()
   {
      if ( $this->request->uid == self::UID_GUEST )
      {
         $this->response->pageRedirect('/app/user/login');
         return;
      }

      $tag = $this->_getTagObj();
      $tagTree = $tag->getTagTree();

      \sizeof( $tagTree[ $tag->id ][ 'children' ] ) ? $this->error( 'Could not post topic in this forum' ) : $this->createTopic( $tag->id );
   }

   public function createTopic( $tid )
   {
      if ( \strlen( $this->request->post[ 'body' ] ) < 5 || \strlen( $this->request->post[ 'title' ] ) < 5 )
      {
         $this->error( 'Topic title or body is too short.' );
      }

      $user = new User( $this->request->uid, 'createTime,points,status' );
      try
      {
         // validate post for houston
         if ( self::$_city->id == 1 )
         {
            $user->validatePost( $this->request->ip, $this->request->timestamp, $this->request->post[ 'body' ], $this->request->post[ 'title' ] );
         }
         $node = new Node();
         $node->tid = $tid;
         $node->uid = $this->request->uid;
         $node->title = $this->request->post[ 'title' ];
         $node->body = $this->request->post[ 'body' ];
         $node->createTime = $this->request->timestamp;
         $node->status = 1;
         $node->add();
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
            if ( \sizeof( $users ) > 1 )
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
               $mailer->body = ' --node-- ' . $this->request->post[ 'title' ] . PHP_EOL . $this->request->post[ 'body' ];
               $mailer->to = $this->config->webmaster;
               $mailer->send();
            }
         }

         $this->logger->error( $e->getMessage() . \PHP_EOL . ' --node-- ' . $this->request->post[ 'title' ] . PHP_EOL . $this->request->post[ 'body' ] );
         $this->error( $e->getMessage() );
      }


      if ( $this->request->post[ 'files' ] )
      {
         $file = new Image();
         $file->cityID = self::$_city->id;
         $file->updateFileList( $this->request->post[ 'files' ], $this->config->path[ 'file' ], $node->id );
         $this->_getCacheEvent( 'ImageUpdate' )->trigger();
      }

      $user->points += 3;
      $user->update( 'points' );

      $this->_getCacheEvent( 'ForumNode' )->trigger();
      $this->_getCacheEvent( 'ForumUpdate', $tid )->trigger();

      if ( $node->tid == 15 )
      {
         $this->_getCacheEvent( 'ImmigrationNode' )->trigger();
      }

      $this->pageRedirect( '/node/' . $node->id );
   }

}

//__END_OF_FILE__
