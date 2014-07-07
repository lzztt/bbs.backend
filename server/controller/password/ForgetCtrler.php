<?php

namespace site\controller\password;

use site\controller\Password;
use site\controller\User;
use site\dbobject\User as UserObject;
use lzx\html\Template;
use lzx\core\Mailer;
use site\dbobject\SecureLink;

class ForgetCtrler extends Password
{

   /**
    * default protected interfaces
    */
   protected function init()
   {
      parent::_init();
      // don't cache password page at page level
      $this->cache->setStatus( FALSE );
   }

   public function run()
   {
      if ( $this->request->uid == self::GUEST_UID )
      {
         $this->forget();
      }
      else
      {
         $this->change();
      }
   }

   /**
    * public interfaces
    */
   public function reset()
   {
      if ( $this->request->uid != self::GUEST_UID )
      {
         $this->error( '错误：用户已经登录，不能重设密码。' );
      }

      $uri = $this->request->buildURI( $this->request->getURIargs( $this->request->uri ) );
      $slink = SecureLink::loadFromRequest( $uri, $this->request->get );
      if ( $slink->exists() )
      {
         if ( empty( $this->request->post ) )
         {
            $this->html->var['content'] = new Template( 'password_reset' );
         }
         else
         {
            if ( empty( $this->request->post['password'] ) )
            {
               $this->error( '请输入密码!' );
            }

            $user = new UserObject();
            $user->id = $uid;
            $user->password = $user->hashPW( $this->request->post['password'] );
            $user->update( 'password' );
            $slink->delete();
            $this->html->var['content'] = '您的密码已经重设成功，请用新密码登录。';
         }
      }
      else
      {
         $this->error( '无效的网址链接!' );
      }
   }

   public function change()
   {
      if ( $this->request->uid == self::GUEST_UID )
      {
         $this->setLoginRedirect( $this->request->uri );
         $this->forward( '/user/login' );
         return;
      }

      if ( empty( $this->request->post ) )
      {
         $this->html->var['content'] = new Template( 'password_change' );
      }
      else
      {
         if ( empty( $this->request->post['password_old'] ) )
         {
            $this->error( '请输入旧密码!' );
         }

         if ( empty( $this->request->post['password_new'] ) )
         {
            $this->error( '请输入新密码!' );
         }

         $user = new UserObject( $this->request->uid, 'username,email,password' );

         if ( $user->exists() )
         {
            $pass = $user->hashPW( $this->request->post['password_old'] );
            if ( $pass == $user->password )
            {
               $user->password = $user->hashPW( $this->request->post['password_new'] );
               $user->update( 'password' );

               // send an email to user
               $m = new Mailer();
               $m->to = $user->email;
               $m->subject = '您在HoustonBBS网的密码已经更改成功';
               $m->body = new Template( 'mail/password_changed' );
               $m->send();

               $this->html->var['content'] = '您的密码已经更改成功。';
            }
            else
            {
               $this->error( '输入的旧密码不正确，无法更改密码!' );
            }
         }
      }
   }

   public function forget()
   {
      if ( empty( $this->request->post ) )
      {
         $this->html->var['content'] = new Template( 'password_forget' );
      }
      else
      {
         if ( empty( $this->request->post['username'] ) )
         {
            $this->error( '请输入您的用户名' );
         }
         if ( !\filter_var( $this->request->post['email'], \FILTER_VALIDATE_EMAIL ) )
         {
            $this->error( '不正确的电子邮箱地址 : ' . $this->request->post['email'] );
         }

         $user = new UserObject();
         $user->username = $this->request->post['username'];
         $user->email = $this->request->post['email'];
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
            $mailer->subject = $user->username . ' 请求重设HoustonBBS的帐号密码';
            $contents = [
               'username' => $user->username,
               'uri' => (string) $this->createSecureLink( $user->id, '/password/reset' ),
               'sitename' => 'HoustonBBS'
            ];
            $mailer->body = new Template( 'mail/password_reset', $contents );
            if ( $mailer->send() )
            {
               $this->html->var['content'] = '重设密码的网址链接已经成功发送到您的注册邮箱 ' . $user->email . ' ，请检查email并且按其中提示重设密码。<br />如果您的收件箱内没有帐号激活的电子邮件，请检查电子邮件的垃圾箱，或者与网站管理员联系。';
            }
            else
            {
               $this->error( '重设密码的网址链接邮寄失败，请联系网站管理员重设密码。' );
            }
         }
         else
         {
            $this->error( '您输入的用户名或者注册邮箱错误，找不到相对应的用户帐号。' );
         }
      }
   }

   /**
    * protected methods
    */
   /**
    * private methods
    */
}

//__END_OF_FILE__