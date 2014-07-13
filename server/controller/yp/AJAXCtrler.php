<?php

namespace site\controller\yp;

use site\controller\YP;
use site\dbobject\Node;

class AJAXCtrler extends YP
{

   public function run()
   {
      // url = /forum/ajax/viewcount?tid=<tid>&nids=<nid>_<nid>_

      $viewCount = [ ];
      if ( $this->args[ 0 ] == 'viewcount' && \strlen( $this->request->get[ 'nids' ] ) > 0 )
      {
         //$tid = \intval($this->request->get['tid']);
         $nids = \explode( '_', $this->request->get[ 'nids' ] );
         foreach ( $nids as $i => $nid )
         {
            if ( \strlen( $nid ) > 0 )
            {
               $nids[ $i ] = (int) $nid;
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