<?php

namespace site\api;

use site\Service;
use site\dbobject\User;
use lzx\core\Mailer;
use lzx\html\Template;

class IdentificationCodeAPI extends Service
{

   /**
    * uri: /api/identificationcode[?action=post]
    * post: username=<username>&email=<email>&&captcha=<captcha>
    */
   public function post()
   {
      // validate captcha
      if ( \strtolower( $this->session->captcha ) != \strtolower( $this->request->post[ 'captcha' ] ) )
      {
         $this->error( '图形验证码错误' );
      }
      unset( $this->session->captcha );

      if ( !$this->request->post[ 'username' ] )
      {
         $this->error( '请输入用户名' );
      }

      if ( !$this->request->post[ 'email' ] )
      {
         $this->error( '请输入注册电子邮箱' );
      }

      $user = new User();
      $user->username = $this->request->post[ 'username' ];
      $user->load( 'email' );

      if ( $user->exists() )
      {
         if ( $user->email != $this->request->post[ 'email' ] )
         {
            $this->error( '您输入的电子邮箱与与此用户的注册邮箱不匹配，请检查是否输入了正确的注册邮箱' );
         }

         // create user action and send out email
         $mailer = new Mailer();
         $mailer->to = $user->email;
         $siteName = \ucfirst( self::$_city->uriName ) . 'BBS';
         $mailer->subject = $user->username . '请求在' . $siteName . '的安全验证码';
         $contents = [
            'username' => $user->username,
            'ident_code' => $this->createIdentCode( $user->id ),
            'sitename' => $siteName
         ];
         $mailer->body = new Template( 'mail/ident_code', $contents );

         if ( $mailer->send() === FALSE )
         {
            $this->error( 'sending email error: ' . $user->email );
         }
         else
         {
            $this->_json( NULL );
         }
      }
      else
      {
         $this->error( '你输入的用户名不存在' );
      }
   }

}

//__END_OF_FILE__
