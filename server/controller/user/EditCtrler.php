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
      if ( $this->request->uid == self::UID_GUEST )
      {
         $this->_displayLogin( $this->request->uri );
         return;
      }

      if ( $this->id && $this->id != $this->request->uid )
      {
         $this->pageForbidden();
      }

      $u = new UserObject( $this->request->uid, NULL );

      if ( empty( $this->request->post ) )
      {
         $u->load();

         if ( $u->exists() )
         {
            $info = [
               'userLinks' => $this->_getUserLinks( '/user/' . $u->id . '/edit' ),
               'username' => $u->username,
               'avatar' => $u->avatar ? $u->avatar : '/data/avatars/avatar0' . mt_rand( 1, 5 ) . '.jpg',
               'qq' => $u->qq,
               'wechat' => $u->wechat,
               'website' => $u->website,
               'firstname' => $u->firstname,
               'lastname' => $u->lastname,
               'sex' => $u->sex,
               'birthday' => \date( 'Y-m-d', $u->birthday ),
               'occupation' => $u->occupation,
               'interests' => $u->interests,
               'aboutme' => $u->favoriteQuotation
            ];

            $this->_var[ 'content' ] = new Template( 'user_edit', $info );
         }
         else
         {
            $this->error( '错误：用户不存在' );
         }
      }
      else
      {
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
               $avatar = '/data/avatars/' . $u->id . '-' . \mt_rand( 0, 999 ) . \image_type_to_extension( $fileInfo[ 2 ] );
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

         $u->birthday = \strtotime( $this->request->post[ 'birthday' ] );

         $u->update();

         $this->_var[ 'content' ] = '您的最新资料已被保存。';

         $this->_getIndependentCache( 'ap' . $u->id )->delete();
      }
   }

}

//__END_OF_FILE__
