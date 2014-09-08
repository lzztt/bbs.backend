<?php

namespace site\controller;

use site\Controller;
use site\dbobject\Tag;

abstract class Forum extends Controller
{

   const NODES_PER_PAGE = 30;

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
            if ( !\array_key_exists( self::$_city->ForumRootID, $tagRoot ) )
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
         $tag = new Tag( self::$_city->ForumRootID, NULL );
      }

      return $tag;
   }

}

//__END_OF_FILE__
