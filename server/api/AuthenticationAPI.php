<?php

namespace site\api;

use site\Service;
use site\dbobject\User;

class AuthenticationAPI extends Service
{

   /**
    * Description of AuthenticationAPI
    *
    * @author ikki
    */
   // check if a user is logged in
   // uri: /api/authentication/<session_id>
   // return: uid
   public function get()
   {
      if ( empty( $this->args ) || $this->args[ 0 ] != $this->session->getSessionID() )
      {
         $this->_json( ['sessionID' => $this->session->getSessionID(), 'uid' => 0 ] );
         return;
      }

      if ( $this->request->uid )
      {
         $user = new User( $this->request->uid, 'username' );
         $this->_json( ['sessionID' => $this->session->getSessionID(), 'uid' => $user->id, 'username' => $user->username, 'role' => $user->getUserGroup() ] );
      }
      else
      {
         $this->_json( ['sessionID' => $this->session->getSessionID(), 'uid' => 0 ] );
      }
   }

   // login a user
   // uri: /api/authentication?action=post
   // post: username=<username>&password=<password>
   // return: session id and uid
   public function post()
   {
      if ( isset( $this->request->post[ 'username' ] ) && isset( $this->request->post[ 'password' ] ) )
      {
         // todo: login times control
         $user = new User();
         if ( $user->login( $this->request->post[ 'username' ], $this->request->post[ 'password' ] ) )
         {
            $this->session->setUserID( $user->id );
            $this->_json( ['sessionID' => $this->session->getSessionID(), 'uid' => $user->id, 'username' => $user->username, 'role' => $user->getUserGroup() ] );
            return;
         }
         else
         {
            $this->logger->info( 'Login Fail: ' . $user->username . ' @ ' . $this->request->ip );
            if ( $user->exists() )
            {
               if ( empty( $user->password ) )
               {
                  $this->error( '用户帐号尚未激活，请使用注册email里的安全验证码来设置初始密码。如有问题请联络网站管理员。' );
               }

               if ( $user->status == 1 )
               {
                  $this->error( '错误的密码。' );
               }
               else
               {
                  $this->error( '用户帐号已被封禁，如有问题请联络网站管理员。' );
               }
            }
            else
            {
               $this->error( '错误的用户名。' );
            }
         }
      }
      else
      {
         $this->error( '请填写用户名和密码。' );
      }
   }

   // logout a user
   // uri: /api/authentication/<session_id>?action=delete
   public function delete()
   {
      if ( empty( $this->args ) || $this->args[ 0 ] != $this->session->getSessionID() )
      {
         $this->forbidden();
      }

      $this->session->clear(); // keep session record but clear session data

      $this->_json( NULL );
   }

}

//__END_OF_FILE__
