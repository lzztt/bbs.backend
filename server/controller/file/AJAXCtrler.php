<?php

namespace site\controller\file;

use site\controller\File;
use site\dbobject\Image;

class AJAXCtrler extends File
{

   public function run()
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
         $config[ 'path' ] = $this->config->path[ 'file' ];
         $config[ 'prefix' ] = $this->request->timestamp . $this->request->uid;
         $res = $fobj->saveFile( $this->request->files, $config );
      }

      if ( \is_string( $res ) )
      {
         $res = ['error' => $res ];
      }
      elseif ( \is_array( $res ) )
      {
         foreach ( $res[ 'error' ] as $i => $f )
         {
            $res[ 'error' ][ $i ][ 'error' ] = $f[ 'error' ];
         }
      }

      $this->ajax( $res );
   }

}

//__END_OF_FILE__
