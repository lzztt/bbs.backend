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
      if ( $this->request->uid == self::GUEST_UID )
      {
         $this->_setLoginRedirect( $this->request->uri );
         $this->_displayLogin();
         return;
      }

      if ( empty( $this->request->post ) )
      {
         $this->html->var[ 'content' ] = new Template( 'password_change' );
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

         $user = new User( $this->request->uid, 'username,email,password' );

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
               $m->subject = '您在HoustonBBS网的密码已经更改成功';
               $m->body = new Template( 'mail/password_changed' );
               $m->send();

               $this->html->var[ 'content' ] = '您的密码已经更改成功。';
            }
            else
            {
               $this->error( '输入的旧密码不正确，无法更改密码!' );
            }
         }
      }
   }

}

//__END_OF_FILE__