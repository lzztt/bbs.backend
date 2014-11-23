<?php

namespace site\api;

use site\Service;
use site\dbobject\User;

class UserAPI extends Service
{

   public function get()
   {
      if ( !$this->request->uid || empty( $this->args ) || !\is_numeric( $this->args[ 0 ] ) )
      {
         $this->forbidden();
      }

      $uid = (int) $this->args[ 0 ];
      $user = new User( $uid, 'username,wechat,qq,website,sex,birthday,relationship,createTime,lastAccessTime,lastAccessIP,avatar,points' );

      $info = $user->toArray();
      unset($info['lastAccessIP']);
      $info[ 'lastAccessCity' ] = $this->request->getLocationFromIP( $user->lastAccessIP );
      $info[ 'topics' ] = $user->getRecentNodes( self::$_city->ForumRootID, 10 );
      $info[ 'comments' ] = $user->getRecentComments( self::$_city->ForumRootID, 10 );

      $this->_json( $info );
   }

   public function put()
   {
      if ( !$this->request->uid || empty( $this->args ) || !\is_numeric( $this->args[ 0 ] ) )
      {
         $this->forbidden();
      }

      $uid = (int) $this->args[ 0 ];

      if ( $uid != $this->request->uid )
      {
         $this->forbidden();
      }

      $u = new User( $this->request->uid, NULL );

      $fields = [
         'wechat' => 'wechat',
         'qq' => 'qq',
         'website' => 'website',
         'firstname' => 'firstname',
         'lastname' => 'lastname',
         'occupation' => 'occupation',
         'interests' => 'interests',
         'favoriteQuotation' => 'aboutme',
         'relationship' => 'relationship'
      ];

      foreach ( $fields as $k => $f )
      {
         $u->$k = \strlen( $this->request->post[ $f ] ) ? $this->request->post[ $f ] : NULL;
      }

      $u->sex = \is_numeric( $this->request->post[ 'sex' ] ) ? (int) $this->request->post[ 'sex' ] : NULL;

      $u->birthday = \strtotime( $this->request->post[ 'birthday' ] );

      $u->update();

      $this->_json( '您的最新资料已被保存。' );

      $this->_getIndependentCache( 'ap' . $u->id )->delete();
   }

}

//__END_OF_FILE__
