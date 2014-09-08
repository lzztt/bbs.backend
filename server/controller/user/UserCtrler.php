<?php

namespace site\controller\user;

use site\controller\User;
use site\dbobject\User as UserObject;
use lzx\html\Template;

class UserCtrler extends User
{

   public function run()
   {
      if ( $this->request->uid == self::UID_GUEST )
      {
         $this->_displayLogin( $this->request->uri );
         return;
      }

      $uid = $this->id ? $this->id : $this->request->uid;
      // user are not allowed to view ADMIN's info
      if ( $uid == self::UID_ADMIN && $this->request->uid != self::UID_ADMIN )
      {
         $this->pageForbidden();
      }

      $user = new UserObject( $uid );
      if ( !$user->exists() )
      {
         $this->error( '错误：用户不存在' );
      }

      if ( $user->status == 0 )
      {
         $this->error( '错误：用户已被删除' );
      }

      $sex = \is_null( $user->sex ) ? '未知' : ( $user->sex == 1 ? '男' : '女');
      if ( $user->birthday )
      {
         $birthday = \substr( \sprintf( '%08u', $user->birthday ), 4, 4 );
         $birthday = \substr( $birthday, 0, 2 ) . '/' . \substr( $birthday, 2, 2 );
      }
      else
      {
         $birthday = '未知';
      }

      $content = [
         'uid' => $uid,
         'username' => $user->username,
         'avatar' => $user->avatar ? $user->avatar : '/data/avatars/avatar0' . \mt_rand( 1, 5 ) . '.jpg',
         'userLinks' => $this->_getUserLinks( '/user/' . $uid, $uid ),
         'pm' => $uid != $this->request->uid ? '/user/' . $uid . '/pm' : '',
         'info' => [
            '微信' => $user->wechat,
            'QQ' => $user->qq,
            '个人网站' => $user->website,
            '性别' => $sex,
            '生日' => $birthday,
            '职业' => $user->occupation,
            '兴趣爱好' => $user->interests,
            '自我介绍' => $user->favoriteQuotation,
            '注册时间' => \date( 'm/d/Y H:i:s T', $user->createTime ),
            '上次登录时间' => \date( 'm/d/Y H:i:s T', $user->lastAccessTime ),
            '上次登录地点' => $this->request->getLocationFromIP( $user->lastAccessIP )
         ],
         'topics' => $user->getRecentNodes( self::$_city->ForumRootID, 10 ),
         'comments' => $user->getRecentComments( self::$_city->ForumRootID, 10 )
      ];

      $this->html->var[ 'content' ] = new Template( 'user_display', $content );
   }

}

//__END_OF_FILE__
