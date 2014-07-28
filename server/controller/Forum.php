<?php

namespace site\controller;

use site\Controller;
use site\dbobject\Tag;

abstract class Forum extends Controller
{

   const NODES_PER_PAGE = 25;

   protected function _getTagObj()
   {
      if ( $this->id )
      {
         $tid = $this->id;
         if ( $tid > 0 )
         {
            $tag = new Tag( $tid, NULL );
            $tag->load( 'id' );

            if ( !$tag->exists() )
            {
               $this->pageNotFound();
            }

            $tagRoot = $tag->getTagRoot();
            if ( !\array_key_exists( Tag::FORUM_ID, $tagRoot ) )
            {
               $this->pageNotFound();
            }
         }
         else
         {
            $this->pageNotFound();
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
