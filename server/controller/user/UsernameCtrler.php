<?php

namespace site\controller\user;

use site\controller\User;
use site\dbobject\User as UserObject;
use site\dbobject\PrivMsg;
use lzx\html\HTMLElement;
use lzx\html\Form;
use lzx\html\TextArea;
use lzx\html\Template;
use lzx\core\Mailer;

class UsernameCtrler extends User
{

   public function run()
   {
      if ( $this->request->uid != self::GUEST_UID )
      {
         $this->request->redirect( '/user' );
      }

      if ( empty( $this->request->post ) )
      {
         $this->html->var[ 'content' ] = new Template( 'user_forgetusername' );
      }
      else
      {
         if ( !\filter_var( $this->request->post[ 'email' ], \FILTER_VALIDATE_EMAIL ) )
         {
            $this->error( 'invalid email address : ' . $this->request->post[ 'email' ] );
         }

         $user = new UserObject();
         $user->email = $this->request->post[ 'email' ];
         $user->load( 'username' );

         if ( $user->exists() )
         {
            $response = '您的注册用户名是: ' . $user->username;
         }
         else
         {
            $response = '未发现使用该注册邮箱的账户，请检查邮箱是否正确: ' . $user->email;
         }
         $this->html->var[ 'content' ] = $response;
      }
   }

}

//__END_OF_FILE__
