<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dataobject;

use lzx\core\DataObject;
use lzx\core\MySQL;
use lzx\core\Logger;

/**
 * @property $fid
 * @property $nid //null for comment attachments
 * @property $cid //commentID, null for node attachments
 * @property $name
 * @property $path
 * @property $list
 */
class Image extends DataObject
{

   public function __construct( $load_id = null, $fields = '' )
   {
      $db = MySQL::getInstance();
      $table = \array_pop( \explode( '\\', __CLASS__ ) );
      parent::__construct( $db, $table, $load_id, $fields );
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
      $errmsg = array(
         \UPLOAD_ERR_INI_SIZE => 'upload_err_ini_size',
         \UPLOAD_ERR_FORM_SIZE => 'upload_err_form_size',
         \UPLOAD_ERR_PARTIAL => 'upload_err_partial',
         \UPLOAD_ERR_NO_FILE => 'upload_err_no_file',
         \UPLOAD_ERR_NO_TMP_DIR => 'upload_err_no_tmp_dir',
         \UPLOAD_ERR_CANT_WRITE => 'upload_err_cant_write',
         102 => 'upload_err_invalid_type',
         103 => 'upload_err_cant_save',
      );

      $errorFile = array( );
      $savedFile = array( );
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
               $errorFile[] = array(
                  'name' => $fileName,
                  'error' => $errmsg[$f['error']],
               );
               if ( $tmpFile )
               {
                  $this->rmTmpFile( $tmpFile );
               }
               continue;
            }

            // check image size
            if ( $f['size'] > $size ) // File Size
            {
               $errorFile[] = array(
                  'name' => $fileName,
                  'error' => $errmsg[\UPLOAD_ERR_INI_SIZE],
               );
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
               $errorFile[] = array(
                  'name' => $fileName,
                  'error' => $errmsg[102],
               );
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
               $errorFile[] = array(
                  'name' => $fileName,
                  'error' => $errmsg[103],
               );
               continue;
            }

            $p = \strrpos( $fileName, '.' );

            $savedFile[] = array(
               'name' => $p > 0 ? \substr( $fileName, 0, $p ) : $fileName,
               'path' => \substr( $savePath, \strlen( $filePath ) )
            );
         }
      }

      return array( 'error' => $errorFile, 'saved' => $savedFile );
   }

   public function updateFileList( array $files, $filePath, $nid, $cid = NULL )
   {
      $nid = \intval( $nid );
      if ( isset( $cid ) ) // comment
      {
         $cid = \intval( $cid );
         $where = 'cid = ' . $cid;
      }
      else // node
      {
         $cid = 'NULL';
         $where = 'nid = ' . $nid . ' AND cid IS NULL';
      }

      $db = $this->_db;
      $fids = array( );
      $insert = array( );

      foreach ( $files as $fid => $file )
      {
         if ( \is_numeric( $fid ) )
         {
            $fid = \intval( $fid );
            // saved files
            $db->query( 'UPDATE Image SET name=' . $db->str( $file['name'] ) . ' WHERE fid=' . $fid );
            $fids[] = $fid;
         }
         else
         {
            // new uploaded files
            try
            {
               $info = \getimagesize( $filePath . $file['path'] );
               $width = $info[0];
               $height = $info[1];
               $insert[] = '(' . $nid . ',' . $cid . ',' . $db->str( $file['name'] ) . ',' . $db->str( $file['path'] ) . ',' . $height . ',' . $width . ')';
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
      $db->query( 'INSERT INTO ImageDeleted (fid, path) SELECT fid, path FROM Image WHERE ' . $where . (\sizeof( $fids ) > 0 ? ' AND fid NOT IN (' . \implode( ',', $fids ) . ')' : '') );
      $db->query( 'DELETE FROM Image WHERE ' . $where . (\sizeof( $fids ) > 0 ? ' AND fid NOT IN (' . \implode( ',', $fids ) . ')' : '') );
      // insert new files
      if ( \sizeof( $insert ) > 0 )
      {
         $db->query( 'INSERT INTO Image (nid,cid,name,path,height,width) VALUES ' . \implode( ',', $insert ) );
      }
   }

   public function getRecentImages()
   {
      return $this->_db->select( '( SELECT nid, name, path, title 
                                   FROM (
                                      SELECT f.nid, f.name, f.path, n.title, @rn := IF( @nid = f.nid, @rn := @rn + 1, 1 ) as rn, @nid := f.nid 
                                      FROM Image AS f 
                                      JOIN Node AS n ON f.nid = n.nid 
                                      WHERE (n.tid = 18 AND n.status = 1) AND f.width >= 600 AND f.height >=300 ORDER BY f.fid DESC
                                   ) AS t
                                   WHERE rn < 3 LIMIT 5 )
                                   UNION
                                   ( SELECT nid, name, path, title 
                                   FROM (
                                      SELECT f.nid, f.name, f.path, n.title, @rn := IF( @nid = f.nid, @rn := @rn + 1, 1 ) as rn, @nid := f.nid 
                                      FROM Image AS f 
                                      JOIN Node AS n ON f.nid = n.nid 
                                      WHERE (n.tid != 18 AND n.status = 1) AND f.width >= 600 AND f.height >=300 ORDER BY f.fid DESC
                                   ) AS t
                                   WHERE rn < 3 LIMIT 5 )' );
   }

}

//__END_OF_FILE__
