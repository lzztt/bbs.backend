<?php

namespace site\controller\user;

use site\controller\User;
use site\dbobject\User as UserObject;
use lzx\html\Template;

class EditCtrler extends User
{

   //logged in user
   public function run()
   {
      if ( $this->request->uid == self::GUEST_UID )
      {
         $this->displayLogin( $this->request->uri );
      }

      $uid = empty( $this->args ) ? $this->request->uid : (int) $this->args[ 0 ];

      if ( $this->request->uid != $uid && $this->request->uid != self::ADMIN_UID )
      {
         $this->request->pageForbidden();
      }

      $u = new UserObject();

      if ( empty( $this->request->post ) )
      {
         $u->id = $uid;
         $u->load();

         if ( $u->exists() )
         {
            if ( $user->birthday )
            {
               $birthday = \sprintf( '%08u', $user->birthday );
               $byear = \substr( $birthday, 0, 4 );
               if ( $byear == '0000' )
               {
                  $byear = NULL;
               }
               $bmonth = \substr( $birthday, 4, 2 );
               $bday = \substr( $birthday, 6, 2 );
            }

            $currentURI = '/user/edit/' . $uid;
            $userLinks = $this->getUserLinks( $uid, $currentURI );
            $info = [
               'action' => $currentURI,
               'userLinks' => $userLinks,
               'username' => $u->username,
               'avatar' => $u->avatar ? $user->avatar : '/data/avatars/avatar0' . mt_rand( 1, 5 ) . '.jpg',
               'qq' => $u->qq,
               'wechat' => $u->wechat,
               'website' => $u->website,
               'firstname' => $u->firstname,
               'lastname' => $u->lastname,
               'sex' => $u->sex,
               'byear' => $byear,
               'bmonth' => $bmonth,
               'bday' => $bday,
               'occupation' => $u->occupation,
               'interests' => $u->interests,
               'aboutme' => $u->favoriteQuotation
            ];

            $this->html->var[ 'content' ] = new Template( 'user_edit', $info );
         }
         else
         {
            $this->error( '错误：用户不存在' );
         }
      }
      else
      {
         $u->id = $uid;

         $file = $this->request->files[ 'avatar' ][ 0 ];
         if ( $file[ 'error' ] == 0 && $file[ 'size' ] > 0 )
         {
            $fileInfo = \getimagesize( $file[ 'tmp_name' ] );
            if ( $fileInfo === FALSE || $fileInfo[ 0 ] > 120 || $fileInfo[ 1 ] > 120 )
            {
               $this->error( '修改头像错误：上传头像图片尺寸太大。最大允许尺寸为 120 x 120 像素。' );
               return;
            }
            else
            {
               $avatar = '/data/avatars/' . $uid . '-' . \mt_rand( 0, 999 ) . \image_type_to_extension( $fileInfo[ 2 ] );
               \move_uploaded_file( $file[ 'tmp_name' ], $this->config->path[ 'file' ] . $avatar );
               $u->avatar = $avatar;
            }
         }

         $fields = [
            'wechat' => 'wechat',
            'qq' => 'qq',
            'website' => 'website',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'occupation' => 'occupation',
            'interests' => 'interests',
            'favoriteQuotation' => 'aboutme',
            'relationship' => 'relationship',
            'signature' => 'signature'
         ];

         foreach ( $fields as $k => $f )
         {
            $u->$k = \strlen( $this->request->post[ $f ] ) ? $this->request->post[ $f ] : NULL;
         }

         $u->sex = \is_numeric( $this->request->post[ 'sex' ] ) ? (int) $this->request->post[ 'sex' ] : NULL;

         $u->birthday = (int) ($this->request->post[ 'byear' ] . $this->request->post[ 'bmonth' ] . $this->request->post[ 'bday' ]);

         $u->update();

         $this->html->var[ 'content' ] = '您的最新资料已被保存。';

         $this->cache->delete( 'authorPanel' . $u->id );
      }
   }

}

//__END_OF_FILE__
