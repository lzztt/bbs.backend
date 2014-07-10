<?php

namespace site\controller;

use site\Controller;
use site\dbobject\Tag;

abstract class Forum extends Controller
{

   const NODES_PER_PAGE = 25;

   protected function _getTagObj()
   {
      if ( \sizeof( $this->args ) > 0 )
      {
         $tid = (int) $this->args[ 0 ];
         if ( $tid > 0 )
         {
            $tag = new Tag( $tid, NULL );
            $tag->load( 'id' );

            if ( !$tag->exists() )
            {
               $this->request->pageNotFound();
            }

            $tagRoot = $tag->getTagRoot();
            if ( !\array_key_exists( Tag::FORUM_ID, $tagRoot ) )
            {
               $this->request->pageNotFound();
            }
         }
         else
         {
            $this->request->pageNotFound();
         }
      }
      else
      {
         // main forum
         $tag = new Tag( Tag::FORUM_ID, NULL );
      }

      return $tag;
   }

}

//__END_OF_FILE__
