<?php

namespace site\controller\user;

use site\controller\User;
use site\dbobject\User as UserObject;
use site\dbobject\PrivMsg;
use lzx\html\HTMLElement;
use lzx\html\Form;
use lzx\html\TextArea;
use lzx\html\Template;
use lzx\core\Mailer;

class PMCtrler extends User
{

   const PM_PER_PAGE = 25;

   public function run()
   {
      if ( $this->request->uid == self::GUEST_UID )
      {
         $this->_displayLogin( $this->request->uri );
      }

      $uid = empty( $this->args ) ? $this->request->uid : (int) $this->args[ 0 ];
      $user = new UserObject( $uid, NULL );

      if ( $user->id == $this->request->uid )
      {
         // show pm mailbox
         $mailbox = \sizeof( $this->args ) > 1 ? $this->args[ 1 ] : 'inbox';

         if ( !\in_array( $mailbox, ['inbox', 'sent' ] ) )
         {
            $this->error( '短信文件夹[' . $mailbox . ']不存在。' );
         }

         $pmCount = $user->getPrivMsgsCount( $mailbox );
         if ( $pmCount == 0 )
         {
            $this->error( $mailbox == 'sent' ? '您的发件箱里还没有短信。' : '您的收件箱里还没有短信。'  );
         }

         $currentURI = '/user/pm/' . $uid;
         $userLinks = $this->_getUserLinks( $uid, $currentURI );

         $activeLink = '/user/pm/' . $uid . '/' . $mailbox;
         $mailBoxLinks = $this->_getMailBoxLinks( $uid, $activeLink );

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
      else
      {
// show send pm to user page
         $user->load( 'username' );

         if ( empty( $this->request->post ) )
         {
// display pm edit form
            $content = [
               'breadcrumb' => $breadcrumb,
               'toUID' => $uid,
               'toName' => $user->username,
               'form_handler' => '/user/' . $uid . '/pm',
            ];
            $form = new Form( [
               'action' => '/user/' . $user->id . '/pm',
               'id' => 'user-pm-send'
               ] );
            $receipt = new HTMLElement( 'div', ['收信人: ', $this->html->link( $user->username, '/user/' . $user->id ) ] );
            $message = new TextArea( 'body', '短信正文', '最少5个字母或3个汉字', TRUE );

            $form->setData( [$receipt, $message->toHTMLElement() ] );
            $form->setButton( ['submit' => '发送短信' ] );
            $this->html->var[ 'content' ] = $form;
         }
         else
         {
            // save pm to database
            if ( \strlen( $this->request->post[ 'body' ] ) < 5 )
            {
               $this->html->var[ 'content' ] = '错误：短信正文需最少5个字母或3个汉字。';
               return;
            }

            $pm = new PrivMsg();
            $pm->fromUID = $this->request->uid;
            $pm->toUID = $user->id;
            $pm->body = $this->request->post[ 'body' ];
            $pm->time = $this->request->timestamp;
            $pm->add();
            $pm->msgID = $pm->id;
            $pm->update( 'msgID' );

            $user->load( 'username,email' );
            $mailer = new Mailer();
            $mailer->to = $user->email;
            $mailer->subject = $user->username . ' 您有一封新的站内短信';
            $mailer->body = $user->username . ' 您有一封新的站内短信' . "\n" . '请登录后点击下面链接阅读' . "\n" . 'http://www.houstonbbs.com/pm/' . $pm->id;
            if ( !$mailer->send() )
            {
               $this->logger->error( 'PM EMAIL REMINDER SENDING ERROR: ' . $pm->id );
            }

            $this->html->var[ 'content' ] = '您的短信已经发送给用户 <i>' . $user->username . '</i>';
         }
      }
   }

   protected function _getPager( $pmCount, $link )
   {
      $pageNo = $this->request->get[ 'page' ] ? (int) $this->request->get[ 'page' ] : 1;
      $pageCount = \ceil( $pmCount / self::PM_PER_PAGE );

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
