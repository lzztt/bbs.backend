<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;
use lzx\core\Logger;

/**
 * @property $id
 * @property $nid //null for comment attachments
 * @property $cid //commentID, null for node attachments
 * @property $name
 * @property $path
 * @property $height
 * @property $width
 */
class Image extends DBObject
{

   public function __construct( $id = null, $properties = '' )
   {
      $db = DB::getInstance();
      $table = 'images';
      $fields = [
         'id' => 'id',
         'nid' => 'nid',
         'cid' => 'cid',
         'name' => 'name',
         'path' => 'path',
         'height' => 'height',
         'width' => 'width'
      ];
      parent::__construct( $db, $table, $fields, $id, $properties );
   }

   private function rmTmpFile( $file )
   {
      try
      {
         \unlink( $file );
      }
      catch ( \Exception $e )
      {
         $logger = Logger::getInstance();
         $logger->error( $e->getMessage() . ' : ' . $file );
      }
   }

   // will always assuming multiple file array
   public function saveFile( array $files, $filePath, $timestamp, $uid, $size = 5120000, $maxWidth = 600, $maxHeight = 960 )
   {
      $errmsg = [
         \UPLOAD_ERR_INI_SIZE => 'upload_err_ini_size',
         \UPLOAD_ERR_FORM_SIZE => 'upload_err_form_size',
         \UPLOAD_ERR_PARTIAL => 'upload_err_partial',
         \UPLOAD_ERR_NO_FILE => 'upload_err_no_file',
         \UPLOAD_ERR_NO_TMP_DIR => 'upload_err_no_tmp_dir',
         \UPLOAD_ERR_CANT_WRITE => 'upload_err_cant_write',
         102 => 'upload_err_invalid_type',
         103 => 'upload_err_cant_save',
      ];

      $errorFile = [];
      $savedFile = [];
      // save files
      foreach ( $files as $type => $fileList )
      {
         $path = $filePath . '/data/' . $type . '/' . $timestamp . \mt_rand( 0, 9 ) . $uid;

         foreach ( $fileList as $i => $f )
         {
            $fileName = $f['name'];
            $tmpFile = $f['tmp_name'];

            // check upload error
            if ( $f['error'] !== \UPLOAD_ERR_OK ) // upload error
            {
               $errorFile[] = [
                  'name' => $fileName,
                  'error' => $errmsg[$f['error']],
               ];
               if ( $tmpFile )
               {
                  $this->rmTmpFile( $tmpFile );
               }
               continue;
            }

            // check image size
            if ( $f['size'] > $size ) // File Size
            {
               $errorFile[] = [
                  'name' => $fileName,
                  'error' => $errmsg[\UPLOAD_ERR_INI_SIZE],
               ];
               if ( $tmpFile )
               {
                  $this->rmTmpFile( $tmpFile );
               }
               continue;
            }


            $imageInfo = \getimagesize( $tmpFile ); // not requiring GD
            // check image type
            if ( $imageInfo === FALSE || $imageInfo[2] > \IMAGETYPE_PNG ) // Image Type: 1 = IMAGETYPE_GIF, 2 = IMAGETYPE_JPEG, 3 = IMAGETYPE_PNG
            {
               $errorFile[] = [
                  'name' => $fileName,
                  'error' => $errmsg[102],
               ];
               if ( $tmpFile )
               {
                  $this->rmTmpFile( $tmpFile );
               }
               continue;
            }

            $savePath = $path . $i . \image_type_to_extension( $imageInfo[2], TRUE ); // not requiring GD
            $width = $imageInfo[0];
            $height = $imageInfo[1];

            // save image
            try
            {
               if ( $width > $maxWidth || $height > $maxHeight )
               {
                  // resize image
                  $im = new \Imagick( $tmpFile );
                  $im->resizeImage( $maxWidth, $maxHeight, \Imagick::FILTER_LANCZOS, 1, TRUE );
                  $im->writeImage( $savePath );
                  $im->clear();
                  if ( $tmpFile )
                  {
                     $this->rmTmpFile( $tmpFile );
                  }
               }
               else
               {
                  // copy image
                  \move_uploaded_file( $tmpFile, $savePath );
               }
            }
            catch ( \Exception $e )
            {
               if ( isset( $im ) )
               {
                  $im->clear();
                  unset( $im );
               }
               if ( $tmpFile )
               {
                  $this->rmTmpFile( $tmpFile );
               }
               $logger = Logger::getInstance();
               $logger->error( $e->getMessage() );
               $errorFile[] = [
                  'name' => $fileName,
                  'error' => $errmsg[103],
               ];
               continue;
            }

            $p = \strrpos( $fileName, '.' );

            $savedFile[] = [
               'name' => $p > 0 ? \substr( $fileName, 0, $p ) : $fileName,
               'path' => \substr( $savePath, \strlen( $filePath ) )
            ];
         }
      }

      return ['error' => $errorFile, 'saved' => $savedFile];
   }

   public function updateFileList( array $files, $filePath, $nid, $cid = NULL )
   {
      $nid = \intval( $nid );
      $isCommment = FALSE;
      if ( isset( $cid ) ) // comment
      {
         $isComment = TRUE;
         $cid = \intval( $cid );
         $arr = $this->call( 'get_comment_images(' . $cid . ')' );
      }
      else
      {
         $cid = 'NULL';
         $arr = $this->call( 'get_node_images(' . $nid . ')' );
      }

      $images = [];
      foreach ( $arr as $r )
      {
         $images[$r['id']] = $r;
      }

      foreach ( $files as $fid => $file )
      {
         if ( \is_numeric( $fid ) )
         {
            $fid = \intval( $fid );
            if ( $file['name'] != $images[$fid]['name'] )
            {
               $this->call( 'update_image(:fid, :name)', [':fid' => $fid, ':name' => $file['name']] );
            }
            unset( $images[$fid] );
         }
         else
         {
            // new uploaded files
            try
            {
               $info = \getimagesize( $filePath . $file['path'] );
               $width = $info[0];
               $height = $info[1];
               $insert[] = '(' . $nid . ',' . $cid . ',"' . $file['name'] . '","' . $file['path'] . '",' . $height . ',' . $width . ')';
            }
            catch ( \Exception $e )
            {
               $logger = Logger::getInstance();
               $logger->error( $e->getMessage() );
               continue;
            }
         }
      }
      // delete old saved files are not in new version
      if ( $images )
      {
         $this->call( 'delete_images("' . \implode( ',', \array_keys( $images ) ) . '")' );
      }

      // insert new files
      if ( \sizeof( $insert ) > 0 )
      {
         $this->call( 'insert_images(:values)', [':values' => \implode( ',', $insert )] );
      }
   }

   public function getRecentImages()
   {
      return $this->call( 'get_recent_images()' );
   }

}

//__END_OF_FILE__
