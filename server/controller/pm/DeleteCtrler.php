<?php

namespace site\controller\pm;

use site\controller\PM;
use site\dbobject\PrivMsg;

class DeleteCtrler extends PM
{

   public function run()
   {
      $topicID = (int) $this->args[ 0 ];
      $messageID = (int) $this->args[ 1 ];

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

      $redirect_uri = $topicID == $messageID ? '/user/pm' : '/pm/' . $topicID;
      $this->request->redirect( $redirect_uri );
   }

}

//__END_OF_FILE__
