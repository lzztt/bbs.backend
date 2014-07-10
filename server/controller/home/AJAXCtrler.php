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
         $r = $node->getNodeStat();

         $r[ 'alexa' ] = \strval( new Template( 'alexa' ) );


         $user = new User();
         $return = \array_merge( $r, $user->getUserStat( $this->request->timestamp - 300 ) );
      }
      catch ( \Exception $e )
      {
         $this->logger->error( $e->getMessage(), $e->getTrace() );
         $return[ 'error' ] = 'ajax_excution_error';
      }

      $this->ajax( $return );
   }

}
