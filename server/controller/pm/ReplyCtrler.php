<?php

namespace site\controller\pm;

use site\controller\PM;
use site\dbobject\PrivMsg;
use site\dbobject\User;
use lzx\core\Mailer;

class ReplyCtrler extends PM
{

   public function run()
   {
      $topicID = $this->id;

      if ( $this->request->uid != $this->request->post[ 'fromUID' ] )
      {
         $this->error( '错误，用户没有权限回复此条短信' );
      }

      if ( \strlen( $this->request->post[ 'body' ] ) < 5 )
      {
         $this->error( '错误：短信正文字数太少。' );
      }

      $user = new User( $this->request->post[ 'toUID' ], 'username,email' );

      if ( !$user->exists() )
      {
         $this->error( '错误：收信人用户不存在。' );
      }

      $pm = new PrivMsg();
      $pm->topicID = $topicID;
      $pm->fromUID = $this->request->uid;
      $pm->toUID = $user->id;
      $pm->body = $this->request->post[ 'body' ];
      $pm->time = $this->request->timestamp;
      $pm->add();

      $mailer = new Mailer();
      $mailer->to = $user->email;
      $mailer->subject = $user->username . ' 您有一封新的站内短信';
      $mailer->body = $user->username . ' 您有一封新的站内短信' . "\n" . '请登录后点击下面链接阅读' . "\n" . 'http://www.houstonbbs.com/pm/' . $pm->topicID;
      if ( !$mailer->send() )
      {
         $this->logger->error( 'PM EMAIL REMINDER SENDING ERROR: ' . $pm->id );
      }

      $this->request->redirect( '/pm/' . $topicID );
   }

}

//__END_OF_FILE__
