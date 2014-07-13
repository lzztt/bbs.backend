<?php

namespace site\controller\user;

use site\controller\User;
use site\dbobject\User as UserObject;
use lzx\html\Template;
use lzx\core\Mailer;

class UsernameCtrler extends User
{

   public function run()
   {
      if ( $this->request->uid != self::GUEST_UID )
      {
         $this->request->redirect( '/user' );
      }

      if ( empty( $this->request->post ) )
      {
         $this->html->var[ 'content' ] = new Template( 'user_forgetusername' );
      }
      else
      {
         if ( !\filter_var( $this->request->post[ 'email' ], \FILTER_VALIDATE_EMAIL ) )
         {
            $this->error( 'invalid email address : ' . $this->request->post[ 'email' ] );
         }

         $user = new UserObject();
         $user->email = $this->request->post[ 'email' ];
         $user->load( 'username' );


         if ( $user->exists() )
         {

            $mailer = new Mailer();
            $mailer->to = $user->email;
            $mailer->subject = $user->username . 'HoustonBBS的账户名';
            $mailer->body = '你在HoustonBBS的用户名是: ' . $user->username;
            
            if ( $mailer->send() )
            {
               $response = '用户名已经成功发送到您的注册邮箱 ' . $user->email . ' ，请检查email。<br />如果您的收件箱内没有此电子邮件，请检查电子邮件的垃圾箱，或者与网站管理员联系。';
            }
            else
            {
               $this->error( '找回用户名邮件发送失败，请联系网站管理员。' );
            }
         }
         else
         {
            $response = '未发现使用该注册邮箱的账户，请检查邮箱是否正确: ' . $user->email;
         }
         $this->html->var[ 'content' ] = $response;
      }
   }

}

//__END_OF_FILE__
