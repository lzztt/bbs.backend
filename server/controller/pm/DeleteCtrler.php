<?php

namespace site\controller\pm;

use site\controller\PM;
use site\dbobject\PrivMsg;

class DeleteCtrler extends PM
{

   public function run()
   {
      $topicID = $this->id;
      $messageID = $this->args ? (int) $this->args[ 0 ] : $topicID;

      $pm = new PrivMsg();
      $pm->id = $messageID;
      try
      {
         $pm->deleteByUser( $this->request->uid );
      }
      catch ( \Exception $e )
      {
         $this->error( 'failed to delete message ' . $messageID . ' as user ' . $this->request->uid );
      }

      $this->redirect = ( $topicID == $messageID ? '/user/pm' : '/pm/' . $topicID );
   }

}

//__END_OF_FILE__
