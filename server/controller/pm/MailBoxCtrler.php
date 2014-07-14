<?php

namespace site\controller\pm;

use site\controller\PM;
use site\dbobject\User;
use lzx\html\Template;

class MailBoxCtrler extends PM
{

   const PM_PER_PAGE = 25;

   public function run()
   {
      $user = new User( $this->request->uid, NULL );

      $mailbox = $this->args ? $this->args[ 0 ] : 'inbox';
      $this->_setMailBox( $mailbox );

      $pmCount = $user->getPrivMsgsCount( $mailbox );
      if ( $pmCount == 0 )
      {
         $this->error( $mailbox == 'sent' ? '您的发件箱里还没有短信。' : '您的收件箱里还没有短信。'  );
      }

      $userLinks = $this->_getUserLinks( '/pm/mailbox' );

      $mailBoxLinks = $this->_getMailBoxLinks( '/pm/mailbox/' . $mailbox );

      list($pageNo, $pager) = $this->_getPager( $pmCount, $activeLink );
      $msgs = $user->getPrivMsgs( $mailbox, 25, ($pageNo - 1) * 25 );

      $thead = ['cells' => ['短信', '联系人', '时间' ] ];
      $tbody = [ ];
      foreach ( $msgs as $i => $m )
      {
         $msgs[ $i ][ 'body' ] = $this->html->truncate( $m[ 'body' ] );
         $msgs[ $i ][ 'time' ] = \date( 'm/d/Y H:i', $m[ 'time' ] );
      }

      $content = [
         'uid' => $user->id,
         'userLinks' => $userLinks,
         'mailBoxLinks' => $mailBoxLinks,
         'pager' => $pager,
         'msgs' => $msgs,
      ];

      $this->html->var[ 'content' ] = new Template( 'pm_list', $content );
   }

   protected function _getPager( $pmCount, $link )
   {
      $pageNo = $this->request->get[ 'page' ] ? (int) $this->request->get[ 'page' ] : 1;
      $pageCount = \ceil( $pmCount / self::TOPIC_PER_PAGE );

      if ( $pageNo < 1 || $pageNo > $pageCount )
      {
         $pageNo = $pageCount;
      }
      return [
         $pageNo,
         $this->html->pager( $pageNo, $pageCount, $link )
      ];
   }

}

//__END_OF_FILE__
