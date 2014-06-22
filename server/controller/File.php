<?php

namespace site\controller;

use site\Controller;
use site\dbobject\Image;

class File extends Controller
{

   protected function _default()
   {
      $this->checkAJAX();
      // just exit on other request
      $this->request->pageExit();
   }

   protected function _ajax()
   {
      // uri = /file/ajax/upload
      $action = $this->args[2];
      return $this->run( $action );
   }

   public function upload()
   {
      if ( $this->request->uid == 0 ) // we simply don't allow guest to post this form
      {
         $res = 'upload_err_permission_denied';
      }
      elseif ( empty( $this->request->files ) )
      {
         $res = 'upload_err_no_file';
      }
      else
      {
         $fobj = new Image();
         $config = $this->config->image;
         $config['path'] = $this->config->path['file'] . '/data/';
         $config['prefix'] = $this->request->timestamp . $this->request->uid;
         $res = $fobj->saveFile( $this->request->files, $config );
      }

      if ( \is_string( $res ) )
      {
         $res = ['error' => $this->l( $res )];
      }
      elseif ( \is_array( $res ) )
      {
         foreach ( $res['error'] as $i => $f )
         {
            $res['error'][$i]['error'] = $this->l( $f['error'] );
         }
      }

      return $res;
   }

   public function delete()
   {
      // if has fid, delete from database
      // delete from file system
   }

}

//__END_OF_FILE__
