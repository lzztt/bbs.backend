<?php

namespace site\controller\node;

use site\controller\Node;
use site\dbobject\Node as NodeObject;

class TagCtrler extends Node
{

   public function run()
   {
      $this->cache->setStatus( FALSE );

      list($nid, $type) = $this->_getNodeType();
      $method = '_tag' . $type;
      $this->$method( $nid );
   }

   private function _tagForumTopic( $nid )
   {
      if ( empty( $this->args ) )
      {
         $this->error( 'no tag id specified' );
      }

      $newTagID = (int) $this->args[ 0 ];

      $nodeObj = new NodeObject( $nid, 'uid,tid' );
      if ( $this->request->uid == 1 || $this->request->uid == $nodeObj->uid )
      {
         $oldTagID = $nodeObj->tid;
         $nodeObj->tid = $newTagID;
         $nodeObj->update( 'tid' );

         $this->cache->delete( '/forum/' . $oldTagID );
         $this->cache->delete( '/forum/' . $newTagID );
         $this->cache->delete( '/node/' . $nid );

         $this->request->redirect( '/node/' . $nid );
      }
      else
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->request->pageForbidden();
      }
   }

}

//__END_OF_FILE__
