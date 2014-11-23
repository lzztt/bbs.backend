<?php

namespace site\controller\pm;

use site\controller\PM;
use site\dbobject\User;
use lzx\html\Template;

class MailBoxCtrler extends PM
{

   public function run()
   {
      if ( $this->request->uid == self::UID_GUEST )
      {
         $this->_displayLogin( $this->request->uri );
         return;
      }

      $user = new User( $this->request->uid, NULL );

      $mailbox = $this->args ? $this->args[ 0 ] : 'inbox';
      $this->session->mailbox = $mailbox;

      $pmCount = $user->getPrivMsgsCount( $mailbox );
      if ( $pmCount == 0 )
      {
         $this->error( $mailbox == 'sent' ? '您的发件箱里还没有短信。' : '您的收件箱里还没有短信。'  );
      }

      $userLinks = $this->_getUserLinks( '/pm/mailbox' );

      $mailBoxLinks = $this->_getMailBoxLinks( '/pm/mailbox/' . $mailbox );

      list($pageNo, $pageCount) = $this->_getPagerInfo( $pmCount, self::TOPIC_PER_PAGE );
      $pager = Template::pager( $pageNo, $pageCount, $activeLink );

      $msgs = $user->getPrivMsgs( $mailbox, self::TOPIC_PER_PAGE, ($pageNo - 1) * self::TOPIC_PER_PAGE );

      $thead = ['cells' => ['短信', '联系人', '时间' ] ];
      $tbody = [ ];
      foreach ( $msgs as $i => $m )
      {
         $msgs[ $i ][ 'time' ] = \date( 'm/d/Y H:i', $m[ 'time' ] );
      }

      $content = [
         'uid' => $user->id,
         'userLinks' => $userLinks,
         'mailBoxLinks' => $mailBoxLinks,
         'pager' => $pager,
         'msgs' => $msgs,
      ];

      $this->_var[ 'content' ] = new Template( 'pm_list', $content );
   }

}

//__END_OF_FILE__
