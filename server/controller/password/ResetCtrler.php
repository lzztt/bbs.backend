<?php

namespace site\controller\password;

use site\controller\Password;
use site\dbobject\User;
use lzx\html\Template;

class ResetCtrler extends Password
{

   /**
    * public interfaces
    */
   public function run()
   {
      if ( $this->request->uid != self::UID_GUEST )
      {
         $this->error( '错误：用户已经登录，不能重设密码。' );
      }

      // validate request!      
      // check if the current page is a secure link page
      $slink = $this->_getSecureLink( $this->request->uri );

      // not an secure link page
      if ( !$slink )
      {
         $this->error( '无效的网址链接!' );
      }

      // secure link
      if ( $this->request->post )
      {
         // have posted data, process
         // we don't have a secure link stored in session, or have one but not the referer
         if ( !$this->session->secureLink || $this->request->referer != $this->session->secureLink )
         {
            $this->error( '无效的网址链接!' );
         }

         if ( empty( $this->request->post[ 'password' ] ) )
         {
            $this->error( '请输入密码!' );
         }

         $user = new User( $slink->uid, 'password,status' );

         if ( !$user->exists() )
         {
            $this->error( '用户不存在，无法重设密码' );
         }

         if ( $user->status === 0 )
         {
            $this->error( '用户被封禁，无法重设密码' );
         }

         // new user activation
         if ( $user->status === NULL && $user->password === NULL )
         {
            $user->status = 1;
            $user->password = $user->hashPW( $this->request->post[ 'password' ] );
            $user->update( 'password,status' );
         }
         else
         {
            $user->password = $user->hashPW( $this->request->post[ 'password' ] );
            $user->update( 'password' );
         }

         unset( $this->session->secureLink );
         $slink->delete();

         $this->html->var[ 'content' ] = '您的密码已经重设成功，请用新密码登录。';
      }
      else
      {
         // no posted data, display form
         // save link uri to session
         $this->session->secureLink = (string) $slink;

         $this->html->var[ 'content' ] = new Template( 'password_reset' );
      }
   }

}

//__END_OF_FILE__