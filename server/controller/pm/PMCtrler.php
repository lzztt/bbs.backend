<?php

namespace site\controller\pm;

use site\controller\PM;
use site\dbobject\PrivMsg;
use lzx\html\Template;

class PMCtrler extends PM
{

   public function run()
   {
      if ( $this->request->uid == self::UID_GUEST )
      {
         $this->_displayLogin( $this->request->uri );
         return;
      }

      if ( !$this->id )
      {
         $this->error( '错误：该条短信不存在。' );
      }

      $topicID = $this->id;

      $pm = new PrivMsg();
      $msgs = $pm->getPMConversation( $topicID, $this->request->uid );
      if ( empty( $msgs ) )
      {
         $this->error( '错误：该条短信不存在。' );
      }

      foreach ( $msgs as $i => $m )
      {
         if ( empty( $m[ 'avatar' ] ) )
         {
            $msgs[ $i ][ 'avatar' ] = '/data/avatars/avatar0' . \mt_rand( 1, 5 ) . '.jpg';
         }
         $msgs[ $i ][ 'time' ] = \date( 'm/d/Y H:i', $m[ 'time' ] );
         $msgs[ $i ][ 'body' ] = \nl2br( $m[ 'body' ] );
      }


      $this->_var[ 'content' ] = new Template( 'pm_topic', [
         'topicID' => $topicID,
         'msgs' => $msgs,
         'fromUID' => $this->request->uid,
         'replyTo' => $pm->getReplyTo( $topicID, $this->request->uid ),
         'userLinks' => $this->_getUserLinks( '/pm/mailbox' ),
         'mailBoxLinks' => $this->_getMailBoxLinks( '/pm/mailbox/' . $this->_getMailBox() )
         ] );
   }

}

//__END_OF_FILE__
