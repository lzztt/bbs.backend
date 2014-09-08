<?php

namespace site\controller\user;

use site\controller\User;
use site\dbobject\User as UserObject;
use site\dbobject\PrivMsg;
use lzx\html\HTMLElement;
use lzx\html\Form;
use lzx\html\TextArea;
use lzx\core\Mailer;

class PMCtrler extends User
{

   public function run()
   {
      if ( $this->request->uid == self::UID_GUEST )
      {
         $this->_displayLogin( $this->request->uri );
         return;
      }

      if ( !$this->id || $this->id == $this->request->uid )
      {
         $this->error( '不能给自己发送站内短信' );
      }

      $user = new UserObject( $this->id, 'username,email' );

      if ( !$user->exists() )
      {
         $this->error( '用户不存在' );
      }

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

         $mailer = new Mailer();
         $mailer->to = $user->email;
         $mailer->subject = $user->username . ' 您有一封新的站内短信';
         $mailer->body = $user->username . ' 您有一封新的站内短信' . "\n" . '请登录后点击下面链接阅读' . "\n" . 'http://' . $this->request->domain . '/pm/' . $pm->id;
         if ( !$mailer->send() )
         {
            $this->logger->error( 'PM EMAIL REMINDER SENDING ERROR: ' . $pm->id );
         }

         $this->html->var[ 'content' ] = '您的短信已经发送给用户 <i>' . $user->username . '</i>';
      }
   }

}

//__END_OF_FILE__
