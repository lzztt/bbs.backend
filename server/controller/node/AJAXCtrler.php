<?php

namespace site\controller\node;

use site\controller\Node;
use lzx\core\BBCode;
use lzx\html\HTMLElement;
use lzx\html\Template;
use lzx\core\Mailer;
use site\dbobject\Node as NodeObject;
use site\dbobject\NodeYellowPage;
use site\dbobject\Comment;
use site\dbobject\Image;
use site\dbobject\User;
use site\dbobject\Activity;
use site\dbobject\Tag;

class AJAXCtrler extends Node
{

   const COMMENTS_PER_PAGE = 10;

   public function run()
   {
      // url = /node/ajax/viewcount?nid=<nid>

      $viewCount = [ ];
      if ( $this->args[ 0 ] == 'viewcount' )
      {
         $nid = \intval( $this->request->get[ 'nid' ] );
         $nodeObj = new NodeObject( $nid, 'viewCount' );
         if ( $nodeObj->exists() )
         {
            $nodeObj->viewCount = $nodeObj->viewCount + 1;
            $nodeObj->update( 'viewCount' );
            $viewCount[ 'viewCount_' . $nid ] = $nodeObj->viewCount;
         }
      }

      $this->ajax( $viewCount );
   }

}

//__END_OF_FILE__
