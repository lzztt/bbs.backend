<?php

namespace site\api;

use site\Service;
use site\dbobject\User;
use lzx\core\Mailer;

class UsernameAPI extends Service
{

   /**
    * new get username request (via email)
    * uri: /api/user/username?action=post
    * post: email=<email>&captcha=<captcha>
    */
   public function post()
   {
      // validate captcha
      if ( \strtolower( $this->session->captcha ) != \strtolower( $this->request->post[ 'captcha' ] ) )
      {
         $this->error( '图形验证码错误' );
      }
      unset( $this->session->captcha );
      
      if ( !\array_key_exists( 'email', $this->request->post ) )
      {
         $this->error( 'please provide an email address: ' . $this->request->uri );
      }

      $email = \filter_var( $this->request->post[ 'email' ], FILTER_VALIDATE_EMAIL );

      if ( !$email )
      {
         $this->error( 'invalid email address: ' . $this->request->post[ 'email' ] );
      }

      $user = new User();
      $user->email = $email;
      $user->load( 'username' );

      if ( $user->exists() )
      {

         $mailer = new Mailer();
         $mailer->to = $user->email;
         $siteName = \ucfirst( self::$_city->uriName ) . 'BBS';
         $mailer->subject = $user->username . '在' . $siteName . '的用户名';
         $mailer->body = '您在' . $siteName . '的用户名是: ' . $user->username;

         if ( $mailer->send() )
         {
            $this->_json( NULL );
         }
         else
         {
            $this->error( '找回用户名邮件发送失败，请联系网站管理员。' );
         }
      }
      else
      {
         $this->error( '未发现使用该注册邮箱的账户，请检查邮箱是否正确: ' . $user->email );
      }
   }

}

//__END_OF_FILE__
