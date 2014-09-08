<?php

namespace site\controller\password;

use site\controller\Password;
use site\dbobject\User;
use lzx\html\Template;
use lzx\core\Mailer;

class ChangeCtrler extends Password
{

   public function run()
   {
      if ( $this->request->uid == self::UID_GUEST )
      {
         $this->_setLoginRedirect( $this->request->uri );
         $this->_displayLogin();
         return;
      }

      $uid = $this->id ? $this->id : $this->request->uid;
      if ( $uid != $this->request->uid && $this->request->uid != self::UID_ADMIN )
      {
         $this->pageForbidden();
      }

      if ( empty( $this->request->post ) )
      {
         $this->html->var[ 'content' ] = new Template( 'password_change', [ 'userLinks' => $this->_getUserLinks( '/password/' . $uid . '/change' ), 'action' => '/password/' . $uid . '/change' ] );
      }
      else
      {
         if ( empty( $this->request->post[ 'password_old' ] ) )
         {
            $this->error( '请输入旧密码!' );
         }

         if ( empty( $this->request->post[ 'password_new' ] ) )
         {
            $this->error( '请输入新密码!' );
         }

         $user = new User( $uid, 'username,email,password' );

         if ( $user->exists() )
         {
            $pass = $user->hashPW( $this->request->post[ 'password_old' ] );
            if ( $pass == $user->password )
            {
               $user->password = $user->hashPW( $this->request->post[ 'password_new' ] );
               $user->update( 'password' );

               // send an email to user
               $m = new Mailer();
               $m->to = $user->email;
               $siteName = \ucfirst( self::$_city->uriName ) . 'BBS';
               $m->subject = '您在' . $siteName . '网的密码已经更改成功';
               $m->body = new Template( 'mail/password_changed', [ 'sitename' => $siteName ] );
               $m->send();

               $this->html->var[ 'content' ] = '您的密码已经更改成功。';
            }
            else
            {
               $this->error( '输入的旧密码不正确，无法更改密码!' );
            }
         }
         else
         {
            $this->error( '用户不存在!' );
         }
      }
   }

}

//__END_OF_FILE__