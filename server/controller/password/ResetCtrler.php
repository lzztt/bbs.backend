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
      if ( $this->request->uid != self::GUEST_UID )
      {
         $this->error( '错误：用户已经登录，不能重设密码。' );
      }

      // validate request!      
      // check if the current page is a secure link page
      $slink = $this->_getSecureLink( $this->request->uri );

      // not an secure link page
      if ( !$slink )
      {
         // check if the referer page is a secure link page
         $slink = $this->_getSecureLink( $this->request->referer );
         // referer is not a secure link either, or not stored in session
         if ( !$slink || $this->request->referer != $this->session->secureLink )
         {
            $this->error( '无效的网址链接!' );
         }
      }

      if ( empty( $this->request->post ) )
      {
         // save link uri to session
         $this->session->secureLink = $slink;

         $this->html->var[ 'content' ] = new Template( 'password_reset' );
      }
      else
      {
         if ( empty( $this->request->post[ 'password' ] ) )
         {
            $this->error( '请输入密码!' );
         }

         $user = new User( $slink->uid, NULL );
         $user->password = $user->hashPW( $this->request->post[ 'password' ] );
         $user->update( 'password' );

         unset( $this->session->secureLink );
         $slink->delete();

         $this->html->var[ 'content' ] = '您的密码已经重设成功，请用新密码登录。';
      }
   }

}

//__END_OF_FILE__