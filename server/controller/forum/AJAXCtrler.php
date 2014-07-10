<?php

namespace site\controller\forum;

use site\controller\Forum;
use lzx\html\Template;
use site\dbobject\Tag;
use site\dbobject\Node;
use site\dbobject\Image;
use site\dbobject\User;

class AJAXCtrler extends Forum
{

   public function run()
   {
      // url = /forum/ajax/viewcount?tid=<tid>&nids=<nid>_<nid>_

      $viewCount = [ ];
      if ( $this->args[ 0 ] == 'viewcount' && \strlen( $this->request->get[ 'nids' ] ) > 0 )
      {
         $tid = \intval( $this->request->get[ 'tid' ] );
         $nids = \explode( '_', $this->request->get[ 'nids' ] );
         foreach ( $nids as $i => $nid )
         {
            if ( \strlen( $nid ) > 0 )
            {
               $nids[ $i ] = \intval( $nid );
            }
            else
            {
               unset( $nids[ $i ] );
            }
         }
         if ( \sizeof( $nids ) > 0 )
         {
            $node = new Node();
            foreach ( $node->getViewCounts( $nids ) as $r )
            {
               $viewCount[ 'viewCount_' . $r[ 'id' ] ] = (int) $r[ 'view_count' ];
            }
         }
      }

      $this->ajax( $viewCount );
   }

}

//__END_OF_FILE__
