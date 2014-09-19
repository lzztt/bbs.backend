<?php

namespace site\controller\home;

use site\controller\Home;
use site\dbobject\Node;
use site\dbobject\User;
use lzx\html\Template;

/**
 * Description of AJAX
 *
 * @author ikki
 */
class AJAXCtrler extends Home
{

   public function run()
   {
      try
      {
         $node = new Node();
         $r = $node->getNodeStat( self::$_city->ForumRootID );

         $r[ 'alexa' ] = (string) new Template( 'alexa' );


         $user = new User();
         $u = $user->getUserStat( $this->request->timestamp - 300, self::$_city->id );
         // make some fake guest :)
         if ( $u[ 'onlineCount' ] > 1 )
         {
            $ratio = self::$_city->id == 1 ? 1.2 : 1.5;
            $u[ 'onlineCount' ] = \ceil( $u[ 'onlineCount' ] * $ratio );
            $u[ 'onlineGuestCount' ] = $u[ 'onlineCount' ] - $u[ 'onlineUserCount' ];
         }
         $return = \array_merge( $r, $u );
      }
      catch ( \Exception $e )
      {
         $this->logger->error( $e->getMessage(), $e->getTrace() );
         $return[ 'error' ] = 'ajax_excution_error';
      }

      $this->ajax( $return );
   }

}

//__END_OF_FILE__
