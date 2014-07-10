<?php

namespace site\controller\node;

use site\controller\Node;
use site\dbobject\Node as NodeObject;
use site\dbobject\User;
use site\dbobject\Activity;

class DeleteCtrler extends Node
{

   public function run()
   {
      $this->cache->setStatus( FALSE );
      
      list($nid, $type) = $this->_getNodeType();
      $method = '_delete' . $type;
      $this->$method( $nid );
   }

   private function _deleteForumTopic( $nid )
   {
      $node = new NodeObject( $nid, 'uid,tid,status' );
      $tags = $node->getTags( $nid );

      if ( !$node->exists() || $node->status == 0 )
      {
         $this->error( 'node does not exist.' );
      }

      if ( $this->request->uid != 1 && $this->request->uid != $node->uid )
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->request->pageForbidden();
      }

      $node->status = 0;
      $node->update( 'status' );

      $activity = new Activity( $nid, 'nid' );
      if ( $activity->exists() )
      {
         $activity->delete();
      }

      $user = new User( $node->uid, 'points' );
      $user->points -= 3;
      $user->update( 'points' );

      $this->cache->delete( '/node/' . $nid );
      $this->cache->delete( '/forum/' . $node->tid );
      $this->request->redirect( '/forum/' . $node->tid );
   }

   private function _deleteYellowPage( $nid )
   {
      if ( $this->request->uid != 1 )
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->request->pageForbidden();
      }
      $node = new NodeObject( $nid, 'tid,status' );
      if ( $node->exists() && $node->status > 0 )
      {
         $node->status = 0;
         $node->update( 'status' );
      }

      $this->cache->delete( '/node/' . $nid );
      $this->request->redirect( '/yp/' . $node->tid );
   }

}

//__END_OF_FILE__
