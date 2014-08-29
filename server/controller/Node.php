<?php

namespace site\controller;

use site\Controller;
use site\dbobject\Node as NodeObject;

abstract class Node extends Controller
{

   const COMMENTS_PER_PAGE = 10;

   protected function _getNodeType()
   {
      $types = [
         $this->_forumRootID[ $this->site ] => 'ForumTopic',
         $this->_ypRootID[ $this->site ] => 'YellowPage',
      ];

      $nid = $this->id;
      if ( $nid <= 0 )
      {
         $this->pageNotFound();
      }

      $nodeObj = new NodeObject();
      $tags = $nodeObj->getTags( $nid );
      if ( empty( $tags ) )
      {
         $this->pageNotFound();
      }

      $rootTagID = \array_shift( \array_keys( $tags ) );

      if ( !\array_key_exists( $rootTagID, $types ) )
      {
         //$this->logger->error( 'wrong root tag : nid = ' . $nid );
         $this->pageNotFound( 'wrong node type' );
      }

      return [$nid, $types[ $rootTagID ] ];
   }

}

//__END_OF_FILE__
