<?php

namespace site\api;

use site\Service;
use site\dbobject\User;
use site\Config;

class UserAPI extends Service
{

   /**
    * get user info
    * uri: /api/user/<uid>
    */
   public function get()
   {
      if ( !$this->request->uid || empty( $this->args ) || !\is_numeric( $this->args[ 0 ] ) )
      {
         $this->forbidden();
      }

      $uid = (int) $this->args[ 0 ];
      $user = new User( $uid, 'username,wechat,qq,website,sex,birthday,relationship,occupation,interests,favoriteQuotation,createTime,lastAccessTime,lastAccessIP,avatar,points,status' );

      if ( $user->status > 0 )
      {
         $info = $user->toArray();
         unset( $info[ 'lastAccessIP' ] );
         $info[ 'lastAccessCity' ] = $this->request->getLocationFromIP( $user->lastAccessIP );
         $info[ 'topics' ] = $user->getRecentNodes( self::$_city->ForumRootID, 10 );
         $info[ 'comments' ] = $user->getRecentComments( self::$_city->ForumRootID, 10 );

         $this->_json( $info );
      }
      else
      {
         $this->error( 'user does not exist' );
      }
   }

   /**
    * update user info
    * As USER:
    * uri: /api/user/<uid>?action=put
    * post: <user properties>
    * 
    * As GUEST:
    * uri: /api/user/<identCode>?action=put
    * post: password=<password>
    */
   public function put()
   {
      if ( empty( $this->args ) || !\is_numeric( $this->args[ 0 ] ) )
      {
         $this->forbidden();
      }

      $uid = 0;
      if ( $this->request->uid )
      {
         $uid = (int) $this->args[ 0 ];

         if ( $uid != $this->request->uid )
         {
            $this->forbidden();
         }
      }
      else
      {
         $uid = $this->parseIdentCode( (int) $this->args[ 0 ] );
         if ( !$uid )
         {
            $this->error( '安全验证码错误，请检查使用邮件里的安全验证码' );
         }
      }

      $u = new User( $uid, NULL );

      if ( \array_key_exists( 'password', $this->request->post ) )
      {
         if ( \array_key_exists( 'password_old', $this->request->post ) )
         {
            // user to change password
            $u->load( 'password' );

            if ( $u->password != $u->hashPW( $this->request->post[ 'password_old' ] ) )
            {
               $this->error( '更改密码失败：输入的旧密码与当前密码不符，请确认输入正确的旧密码' );
            }

            if ( $this->request->post[ 'password' ] != $this->request->post[ 'password2' ] )
            {
               $this->error( '更改密码失败：两次输入的新密码不一致' );
            }

            unset( $this->request->post[ 'password_old' ] );
            unset( $this->request->post[ 'password2' ] );
         }

         $this->request->post[ 'password' ] = $u->hashPW( $this->request->post[ 'password' ] );
      }

      if ( \array_key_exists( 'avatar', $this->request->post ) )
      {
         $image = \base64_decode( \substr( $this->request->post[ 'avatar' ], \strpos( $this->request->post[ 'avatar' ], ',' ) + 1 ) );
         if ( $image !== FALSE )
         {
            $config = Config::getInstance();
            $avatarFile = '/data/avatars/' . $this->request->uid . '_' . ($this->request->timestamp % 100) . '.png';
            \file_put_contents( $config->path[ 'file' ] . $avatarFile, $image );
            $this->request->post[ 'avatar' ] = $avatarFile;
         }
         else
         {
            unset( $this->request->post[ 'avatar' ] );
         }
      }

      foreach ( $this->request->post as $k => $v )
      {
         $u->$k = $v;
      }

      $u->update();

      $this->_json( NULL );

      $this->_getIndependentCache( 'ap' . $u->id )->delete();
   }

   /**
    * uri: /api/user[?action=post]
    * post: username=<username>&email=<email>&captcha=<captcha>
    */
   public function post()
   {
      // validate captcha
      if ( \strtolower( $this->session->captcha ) != \strtolower( $this->request->post[ 'captcha' ] ) )
      {
         $this->error( '图形验证码错误' );
      }
      unset( $this->session->captcha );

      // check username and email first
      if ( empty( $this->request->post[ 'username' ] ) )
      {
         $this->error( '请填写用户名' );
      }
      else
      {
         $username = \strtolower( $this->request->post[ 'username' ] );
         if ( \strpos( $username, 'admin' ) !== FALSE || \strpos( $username, 'bbs' ) !== FALSE )
         {
            $this->error( '不合法的用户名，请选择其他用户名' );
         }
      }

      if ( !\filter_var( $this->request->post[ 'email' ], \FILTER_VALIDATE_EMAIL ) )
      {
         $this->error( '不合法的电子邮箱 : ' . $this->request->post[ 'email' ] );
      }

      if ( $this->_isSpammer( $this->request->post[ 'email' ], $this->request->ip ) )
      {
         $this->logger->info( 'STOP SPAMMER : ' . \implode( '|', [ $this->request->post[ 'username' ], $this->request->post[ 'email' ], $this->request->ip ] ) );
         $this->error( '系统检测到可能存在的注册机器人，所以不能提交您的注册申请。如果您认为这是一个错误的判断，请与网站管理员联系。' );
      }

      if ( isset( $this->request->post[ 'submit' ] ) || $this->_isBot( $this->request->post[ 'email' ] ) )
      {
         $this->logger->info( 'STOP SPAMBOT : ' . $this->request->post[ 'email' ] );
         $this->error( '系统检测到可能存在的注册机器人，所以不能提交您的注册申请。如果您认为这是一个错误的判断，请与网站管理员联系。' );
      }

      $user = new User();
      $user->username = $this->request->post[ 'username' ];
      $user->password = NULL;
      $user->email = $this->request->post[ 'email' ];
      $user->lastAccessIP = (int) \ip2long( $this->request->ip );
      $user->cid = self::$_city->id;
      $user->status = 1;

      // if user record exist, means this is a new-registered user, but need to re-send identification code
      $user->load( 'id' );
      if ( !$user->exists() )
      {
         $user->createTime = $this->request->timestamp;

         // spammer from Nanning
         $geo = \geoip_record_by_name( $this->request->ip );
         // from Nanning
         if ( $geo && $geo[ 'city' ] === 'Nanning' )
         {
            // mark as disabled
            $user->status = 0;
         }

         try
         {
            $user->add();
         }
         catch ( \PDOException $e )
         {
            $this->error( $e->errorInfo[ 2 ] );
         }
      }

      // create user action and send out email
      if ( $this->sendIdentCode( $user ) === FALSE )
      {
         $this->error( 'sending email error: ' . $user->email );
      }
      else
      {
         $this->_json( NULL );
      }
   }

   /**
    * uri: /api/user/<uid>?action=delete
    */
   public function delete()
   {
      if ( $this->request->uid != 1 || empty( $this->args ) || !\is_numeric( $this->args[ 0 ] ) )
      {
         $this->forbidden();
      }

      $uid = (int) $this->args[ 0 ];

      // not allowed to delete admin user
      if ( $uid == 1 )
      {
         $this->forbidden();
      }

      $user = new User();
      $user->id = $uid;
      $user->delete();

      foreach ( $user->getAllNodeIDs() as $nid )
      {
         $this->_getIndependentCache( '/node/' . $nid )->delete();
      }
      $this->_json( NULL );
   }

   private function _isSpammer( $email, $ip )
   {
      $geo = \geoip_record_by_name( $ip );

      if ( \preg_match( '/[a-z][0-9]+[a-z]/', \strstr( $email, '@', true ) ) && (!$geo || $geo[ 'region' ] != 'TX' ) )
      {
         return TRUE;
      }

      return FALSE;
   }

   private function _isBot( $m )
   {
      $try1 = \unserialize( $this->request->curlGetData( 'http://www.stopforumspam.com/api?f=serial&email=' . $m ) );
      if ( $try1[ 'email' ][ 'appears' ] == 1 )
      {
         return TRUE;
      }
      $try2 = $this->request->curlGetData( 'http://botscout.com/test/?mail=' . $m );
      if ( $try2[ 0 ] == 'Y' )
      {
         return TRUE;
      }
      return FALSE;
   }

}

//__END_OF_FILE__
