<?php

namespace site\controller;

use site\Controller;
use site\dbobject\User;
use lzx\html\Template;
use lzx\core\Mailer;
use site\dbobject\UserAction;

class Password extends Controller
{

   public function _default()
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

   public function reset()
   {
      if ( $this->request->uid != self::GUEST_UID )
      {
         $this->error( '错误：用户已经登录，不能注册新用户' );
      }

      $uid = $this->_validateUser();
      if ( $uid === FALSE )
      {
         $this->error( '无效的网址链接!' );
      }

      if ( empty( $this->request->post ) )
      {
         $this->html->var['content'] = new Template( 'password_reset' );
         ;
      }
      else
      {
         
      }
   }

   private function _validateUser()
   {
      if ( !( isset( $this->request->get['r'] ) && $this->request->get['c'] && $this->request->get['t'] ) )
      {
         return FALSE;
      }

      $action = new UserAction();
      $action->id = $this->request->get['r'];
      $action->code = $this->request->get['c'];
      $action->time = $this->request->get['t'];
      $action->load( 'uid' );

      return $action->exists() ? $action->uid : FALSE;
   }

   public function change()
   {
      if ( $this->request->uid == self::GUEST_UID )
      {
         $this->request->pageNotFound();
      }

      if ( empty( $this->request->post ) )
      {
         $this->html->var['content'] = new Template( 'change_password' );
      }
      else
      {
         if ( empty( $this->request->post['old_password'] ) )
         {
            $this->error( '请输入旧密码!' );
         }

         if ( empty( $this->request->post['new_password'] ) )
         {
            $this->error( '请输入新密码!' );
         }

         $user = new User( $this->request->uid, 'password' );

         if ( $user->exists() )
         {
            $pass = $user->hashPW($this->request->post['old_password']);
            if( $pass == $user->password )
            {
               $user->password = $user->hashPW($this->request->post['new_password']);
               $user->update('password');
               $this->html->var['content'] = 'The password has been changed successfully.';
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
         $link_tabs = $this->_link_tabs( '/user/password' );
         $form = new Form( [
            'action' => '/user/password',
            'id' => 'user-pass'
            ] );
         $username = new Input( 'username', '用户名', '输入您的用户名', TRUE );
         $email = new Input( 'email', '注册电子邮箱地址', '输入您注册时使用的电子邮箱地址', TRUE );
         $email->type = 'email';
         $form->setData( [$username->toHTMLElement(), $email->toHTMLElement()] );
         $form->setButton( ['submit' => '发送重设密码链接'] );

         $this->html->var['content'] = $link_tabs . $form;
      }
      else
      {
         if ( empty( $this->request->post['username'] ) )
         {
            $this->error( 'empty username' );
         }
         if ( !\filter_var( $this->request->post['email'], \FILTER_VALIDATE_EMAIL ) )
         {
            $this->error( 'invalid email address : ' . $this->request->post['email'] );
         }

         $user = new UserObject();
         $user->username = $this->request->post['username'];
         $user->email = $this->request->post['email'];
         $user->load( 'id,username,email,status' );
         if ( $user->status === NULL )
         {
            $this->error( '该帐号等待激活中，未激活前不能修改密码' );
         }
         if ( $user->status === 0 )
         {
            $this->error( '该帐号已被封禁，不能修改密码' );
         }
         if ( $user->exists() )
         {
            $password = $user->randomPW();
            $user->password = $user->hashPW( $password );
            $user->update( 'password' );

            $mailer = new Mailer();

            $mailer->to = $user->email;
            $mailer->subject = $user->username . ' 请求重设密码';
            $contents = [
               'username' => $user->username,
               'uri' => $this->_createUser( $user->id, '/user/password/update' ),
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
      }
   }

}
