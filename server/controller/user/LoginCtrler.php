<?php

namespace site\controller\user;

use site\controller\User;
use site\dbobject\User as UserObject;

class LoginCtrler extends User
{

   public function run()
   {
      if ( $this->request->uid != self::UID_GUEST )
      {
         $this->error( '错误：您已经成功登录，不能重复登录。' );
      }

      if ( isset( $this->request->post[ 'username' ] ) && isset( $this->request->post[ 'password' ] ) )
      {
         // todo: login times control
         $user = new UserObject();
         if ( $user->login( $this->request->post[ 'username' ], $this->request->post[ 'password' ] ) )
         {
            $this->_setUser( $user );
            $this->response->setContent( '<script>location.reload();</script>' );
         }
         else
         {
            $this->logger->info( 'Login Fail: ' . $user->username . ' @ ' . $this->request->ip );
            if ( isset( $user->id ) )
            {
               if ( $user->status == 1 )
               {
                  $this->error( '错误：错误的密码。' );
               }
               elseif ( $user->status == 0 )
               {
                  $this->error( '错误：用户帐号已被封禁，如有问题请联络网站管理员。' );
               }
               else
               {
                  $this->error( '错误：用户帐号尚未激活，请点击注册email里的激活链接激活帐号，如有问题请联络网站管理员。' );
               }
            }
            else
            {
               $this->error( '错误：错误的用户名。' );
            }
         }
      }
      else
      {
         $this->error( '错误：请填写用户名和密码。' );
      }
   }

}

//__END_OF_FILE__
