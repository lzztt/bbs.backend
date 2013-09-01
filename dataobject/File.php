<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dataobject;

use lzx\core\DataObject;
use lzx\core\MySQL;
use lzx\core\Request;
use lzx\core\Logger;

/**
 * @property $fid
 * @property $nid //null for comment attachments
 * @property $cid //commentID, null for node attachments
 * @property $name
 * @property $path
 * @property $list
 */
class File extends DataObject
{

   public function __construct($load_id = null, $fields = '')
   {
      $db = MySQL::getInstance();

      parent::__construct($db, 'files', $load_id, $fields);
   }

   // will always assuming multiple file array
   public function saveFile($files, $filePath, $timestamp, $uid, $size = 5120000, $maxWidth = 600, $maxHeight = 960)
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

      $errorFile = array();
      $savedFile = array();
      // save files
      foreach ($files as $type => $fileList)
      {
         $path = $filePath . '/data/' . $type . '/' . $timestamp . \mt_rand(0, 9) . $uid;

         foreach ($fileList as $i => $f)
         {
            $fileName = $f['name'];

            // check upload error
            if ($f['error'] !== \UPLOAD_ERR_OK) // upload error
            {
               $errorFile[] = array(
                  'name' => $fileName,
                  'error' => $errmsg[$f['error']],
               );
               continue;
            }

            // check image size
            if ($f['size'] > $size) // File Size
            {
               $errorFile[] = array(
                  'name' => $fileName,
                  'error' => $errmsg[\UPLOAD_ERR_INI_SIZE],
               );
               continue;
            }

            $tmpFile = $f['tmp_name'];
            $imageInfo = \getimagesize($tmpFile); // not requiring GD
            // check image type
            if ($imageInfo === FALSE || $imageInfo[2] > \IMAGETYPE_PNG) // Image Type: 1 = IMAGETYPE_GIF, 2 = IMAGETYPE_JPEG, 3 = IMAGETYPE_PNG
            {
               $errorFile[] = array(
                  'name' => $fileName,
                  'error' => $errmsg[102],
               );
               continue;
            }

            $savePath = $path . $i . \image_type_to_extension($imageInfo[2], TRUE); // not requiring GD
            $width = $imageInfo[0];
            $height = $imageInfo[1];

            // save image
            try
            {
               if ($width > $maxWidth || $height > $maxHeight)
               {
                  // resize image
                  $im = new \Imagick($tmpFile);
                  $im->resizeImage($maxWidth, $maxHeight, \Imagick::FILTER_LANCZOS, 1, TRUE);
                  $im->writeImage($savePath);
                  $im->destroy();
               }
               else
               {
                  // copy image
                  \move_uploaded_file($tmpFile, $savePath);
               }
            }
            catch (\Exception $e)
            {
               if (isset($im))
               {
                  unset($im);
               }
               $logger = Logger::getInstance();
               $logger->error($e->getMessage());
               $errorFile[] = array(
                  'name' => $fileName,
                  'error' => $errmsg[103],
               );
               continue;
            }

            $p = \strrpos($fileName, '.');

            $savedFile[] = array(
               'name' => $p > 0 ? \substr($fileName, 0, $p) : $fileName,
               'path' => \substr($savePath, \strlen($filePath))
            );
         }
      }

      return array('error' => $errorFile, 'saved' => $savedFile);
   }

   public function updateFileList(array $files, $nid, $cid = NULL)
   {
      $nid = \intval($nid);
      if (isset($cid)) // comment
      {
         $cid = \intval($cid);
         $where = 'cid = ' . $cid;
      }
      else // node
      {
         $cid = 'NULL';
         $where = 'nid = ' . $nid . ' AND cid IS NULL';
      }

      $db = $this->_db;
      $fids = array();
      $insert = array();

      foreach ($files as $fid => $file)
      {
         if (\is_numeric($fid))
         {
            $fid = \intval($fid);
            // saved files
            $db->query('UPDATE files SET name=' . $db->str($file['name']) . ' WHERE fid=' . $fid);
            $fids[] = $fid;
         }
         else
         {
            // new uploaded files
            $insert[] = '(' . $nid . ',' . $cid . ',' . $db->str($file['name']) . ',' . $db->str($file['path']) . ')';
         }
      }
      // delete old saved files are not in new version
      $db->query('INSERT INTO files_deleted (fid, path) SELECT fid, path FROM files WHERE ' . $where . (\sizeof($fids) > 0 ? ' AND fid NOT IN (' . \implode(',', $fids) . ')' : ''));
      $db->query('DELETE FROM files WHERE ' . $where . (\sizeof($fids) > 0 ? ' AND fid NOT IN (' . \implode(',', $fids) . ')' : ''));
      // insert new files
      if (\sizeof($insert) > 0)
      {
         $db->query('INSERT INTO files (nid,cid,name,path) VALUES ' . \implode(',', $insert));
      }
   }

   public function getRecentImages($file_path)
   {
      $arr0 = $this->_db->select('SELECT f.nid, f.name, f.path, n.title FROM files AS f JOIN nodes AS n ON f.nid = n.nid WHERE (n.tid = 18 AND n.status = 1) ORDER BY f.fid DESC LIMIT 15');
      $arr1 = $this->_db->select('SELECT f.nid, f.name, f.path, n.title FROM files AS f JOIN nodes AS n ON f.nid = n.nid WHERE (n.tid != 18 AND n.status = 1) ORDER BY f.fid DESC LIMIT 30');
      $images0 = $this->_image($arr0, $file_path, 5);
      $images1 = $this->_image($arr1, $file_path, 5);
      // YING
      $found = false;
      foreach ( $images1 as $i )
      {
         if ( $i['nid'] == 32902 )
         {
            $found = true;
            break;
         }
      }
      if ( !$found )
      {
         $arr2 = $this->_db->select( 'SELECT f.nid, f.name, f.path, n.title FROM files AS f JOIN nodes AS n ON f.nid = n.nid WHERE f.nid = 32902' );
         \shuffle( $arr2 );
         $images2 = $this->_image( $arr2, $file_path, 1 );
         $images1[4] = $images2[0];
      }
      $images = \array_merge($images0, $images1);

      return $images;
   }

   private function _image(array $files, $file_path, $count)
   {
      $images = array();
      $failed = array();
      $_count = 0;
      foreach ($files as $f)
      {
         try
         {
            $info = getimagesize($file_path . $f['path']);
            if ($info[0] >= 600 && $info[1] >= 300)
            {
               $images[] = $f;
               $_count++;
            }
         }
         catch (\Exception $e)
         {
            $failed[] = $file_path . $f['path'];
         }

         if ($_count >= $count)
         {
            break;
         }
      }

      if (\sizeof($failed) > 0)
      {
         throw new \Exception('failed to check image file : ' . PHP_EOL . implode(PHP_EOL, $failed));
      }
      return $images;
   }

}

//__END_OF_FILE__
