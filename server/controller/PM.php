<?php

namespace site\controller;

use site\Controller;
use lzx\core\Request;
use lzx\html\Template;
use site\Config;
use lzx\core\Logger;
use lzx\core\Session;
use lzx\core\Cookie;

abstract class PM extends Controller
{

   const TOPIC_PER_PAGE = 25;

   protected function _getMailBox()
   {
      if ( $this->response->cookie->mailbox )
      {
         if ( !\in_array( $this->response->cookie->mailbox, ['inbox', 'sent' ] ) )
         {
            $this->error( '短信文件夹[' . $this->response->cookie->mailbox . ']不存在。' );
         }
      }
      else
      {
         $this->response->cookie->mailbox = 'inbox';
      }

      return $this->response->cookie->mailbox;
   }

   protected function _setMailBox( $mailbox )
   {
      if ( $mailbox != $this->response->cookie->mailbox )
      {
         if ( !\in_array( $mailbox, ['inbox', 'sent' ] ) )
         {
            $this->error( '短信文件夹[' . $mailbox . ']不存在。' );
         }

         // $this->response->cookie->mailbox = $mailbox;
      }
   }

   protected function _getMailBoxLinks( $activeLink )
   {
      return Template::navbar( [
            '收件箱' => '/pm/mailbox/inbox',
            '发件箱' => '/pm/mailbox/sent'
            ], $activeLink
      );
   }

}

//__END_OF_FILE__
