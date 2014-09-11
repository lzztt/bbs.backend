<?php

namespace site\controller\sendmail;

use site\controller\SendMail;
use lzx\html\Template;
use lzx\core\Mailer;
use site\dbobject\User;
use site\dbobject\DallasEmail;

class SendMailCtrler extends SendMail
{

   public function run()
   {
      if ( $this->request->uid == self::UID_GUEST )
      {
         $this->pageNotFound();
      }

      // only for gaara and roger83
      $user = new User( $this->request->uid, 'type' );
      if ( !$user->type )
      {
         $this->pageNotFound();
      }

      // send email if has post data
      if ( $this->request->post )
      {
         $message = $this->send( $this->request->post );
      }

      // display page
      $this->html->var[ 'content' ] = new Template( 'send_mail', [ 'message' => $message ] );
   }

   public function send( array $post )
   {
      if ( !\filter_var( $post[ 'email' ], \FILTER_VALIDATE_EMAIL ) )
      {
         $this->error( '不合法的电子邮箱 : ' . $post[ 'email' ] );
      }

      $email = new DallasEmail( $post[ 'email' ] );
      if ( !$email->exists() )
      {
         $mailer = new Mailer( 'care' );
         $mailer->to = $post[ 'email' ];
         $mailer->subject = 'DallasBBS 达拉斯地区免费信息发布';
         $mailer->body = new Template( 'mail/dallas_email', ['name' => $post[ 'name' ] ] );

         if ( $mailer->send() === FALSE )
         {
            $this->error( 'FAIL: ' . $post[ 'email' ] . ' : ' . $post[ 'name' ] );
         }

         $email->email = $post[ 'email' ];
         $email->name = $post[ 'name' ];
         $email->time = $this->request->timestamp;
         $email->add();

         return 'SUCCESS: ' . $post[ 'email' ] . ' : ' . $post[ 'name' ];
      }
      else
      {
         return 'SKIP: ' . $post[ 'email' ] . ' : ' . $post[ 'name' ];
      }
   }

}

//__END_OF_FILE__
