<?php

namespace site\controller\password;

use site\controller\Password;
use site\dbobject\User;
use lzx\html\Template;
use lzx\core\Mailer;

class ForgetCtrler extends Password
{

   public function run()
   {
      if ( $this->request->uid != self::UID_GUEST )
      {
         $this->_forward( '/password/change' );
         return;
      }

      if ( empty( $this->request->post ) )
      {
         $this->html->var[ 'content' ] = new Template( 'password_forget', [ 'userLinks' => $this->_getUserLinks( '/password/forget' ) ] );
      }
      else
      {
         if ( empty( $this->request->post[ 'username' ] ) )
         {
            $this->error( '请输入您的用户名' );
         }
         if ( !\filter_var( $this->request->post[ 'email' ], \FILTER_VALIDATE_EMAIL ) )
         {
            $this->error( '不正确的电子邮箱地址 : ' . $this->request->post[ 'email' ] );
         }

         $user = new User();
         $user->username = $this->request->post[ 'username' ];
         $user->email = $this->request->post[ 'email' ];
         $user->load( 'id,username,email,status' );
         if ( $user->exists() )
         {
            if ( $user->status === NULL )
            {
               $this->error( '该帐号尚未激活，请通过激活邮件里的链接激活您的帐号并设置初始密码' );
            }
            if ( $user->status === 0 )
            {
               $this->error( '该帐号已被封禁，不能修改密码' );
            }

            $mailer = new Mailer();
            $mailer->to = $user->email;
            $siteName = \ucfirst( self::$_city->uriName ) . 'BBS';
            $mailer->subject = $user->username . ' 请求重设' . $siteName . '的帐号密码';
            $contents = [
               'username' => $user->username,
               'uri' => (string) $this->_createSecureLink( $user->id, '/password/reset' ),
               'domain' => $this->request->domain,
               'sitename' => $siteName
            ];
            $mailer->body = new Template( 'mail/password_reset', $contents );
            if ( $mailer->send() )
            {
               $this->html->var[ 'content' ] = '重设密码的网址链接已经成功发送到您的注册邮箱 ' . $user->email . ' ，请检查email并且按其中提示重设密码。<br />如果您的收件箱内没有帐号激活的电子邮件，请检查电子邮件的垃圾箱，或者与网站管理员联系。';
            }
            else
            {
               $this->error( '重设密码的网址链接发送失败，请联系网站管理员重设密码。' );
            }
         }
         else
         {
            $this->error( '您输入的用户名或者注册邮箱错误，找不到相对应的用户帐号。' );
         }
      }
   }

}

//__END_OF_FILE__