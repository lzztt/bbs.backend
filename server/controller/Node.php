<?php

namespace site\controller;

use site\Controller;
use site\dbobject\Node as NodeObject;
use site\dbobject\Tag;

abstract class Node extends Controller
{

   const COMMENTS_PER_PAGE = 10;

   protected function _getNodeType()
   {
      $nid = (int) $this->args[ 0 ];
      if ( $nid <= 0 )
      {
         $this->request->pageNotFound();
      }

      $nodeObj = new NodeObject();
      $tags = $nodeObj->getTags( $nid );
      if ( empty( $tags ) )
      {
         $this->request->pageNotFound();
      }

      $rootTagID = \array_shift( \array_keys( $tags ) );

      $types = [
         Tag::FORUM_ID => 'ForumTopic',
         Tag::YP_ID => 'YellowPage',
      ];

      if ( !\array_key_exists( $rootTagID, $types ) )
      {
         $this->logger->error( 'wrong root tag : nid = ' . $nid );
         $this->error( 'wrong node type' );
      }

      return [$nid, $types[ $rootTagID ] ];
   }

}

//__END_OF_FILE__
