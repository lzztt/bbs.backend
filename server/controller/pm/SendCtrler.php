<?php

namespace site\controller\pm;

use site\controller\PM;
use site\dbobject\PrivMsg;
use site\dbobject\User;
use lzx\core\Mailer;

class SendCtrler extends PM
{

   public function run()
   {
      if ( $this->request->uid == self::UID_GUEST )
      {
         $this->error( '错误：您必须先登录，才能发送站内短信。' );
      }

      $uid = (int) $this->args[ 0 ];
      if ( $uid )
      {
         if ( $uid == $this->request->uid )
         {
            $this->error( '错误：不能给自己发送站内短信' );
         }
      }
      else
      {
         $this->error( '错误：用户不存在' );
      }

      $user = new User( $uid, 'username,email' );

      if ( !$user->exists() )
      {
         $this->error( '错误：用户不存在' );
      }

      // save pm to database
      if ( \strlen( $this->request->post[ 'body' ] ) < 5 )
      {
         $this->error( '错误：短信正文需最少5个字母或3个汉字。' );
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

      $this->html = '您的短信已经发送给用户 <i>' . $user->username . '</i>';
   }

}

//__END_OF_FILE__
