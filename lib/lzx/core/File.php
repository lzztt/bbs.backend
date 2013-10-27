<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace lzx\core;

/**
 * Description of File
 *
 * @author ikki
 * @property Config @config
 */
class File
{

   const NO_FILE = 700;
   const INVALID_TYPE = 701;
   const INVALID_SIZE = 702;

   public $path;
   public $name;
   public $size;

   public function __construct($path = NULL)
   {
      if(\is_file($path))
      {
         $this->path = $path;
         $this->name = \basename($path);
         $this->type = \substr(\strrchr($this->name,'.'), 1); // file extension
         $this->size = \filesize($path);
      }
   }

   public function isWebImage()
   {
      $fileInfo = \getimagesize($this->path);

      if ($fileInfo === FALSE || $fileInfo[2] > IMAGETYPE_PNG) // Image Type: 1 = IMAGETYPE_GIF, 2 = IMAGETYPE_JPEG, 3 = IMAGETYPE_PNG
      {
         return FALSE;
      }
      else
      {
         return TRUE;
      }
   }
/*
   public function saveUploadedFile($allowed_types = array())
   {
      if (empty($_FILES))
      {
         return self::NO_FILE;
      }

      foreach ($_FILES as $type => $file) // only process one file, will return after process the first file
      {
         if ($file['error'] !== UPLOAD_ERR_OK) // upload error
         {
            return $file['error'];
            $errmsg = array(
               UPLOAD_ERR_INI_SIZE => 'The uploaded file size exceeds the upload_max_filesize',
               UPLOAD_ERR_FORM_SIZE => 'The uploaded file size exceeds the MAX_FILE_SIZE',
               UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded. Please try again',
               UPLOAD_ERR_NO_FILE => 'No file was uploaded. Please select a file',
               UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder. Please try again',
               UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk. Please try again'
            );
            return 'File Upload Server Error: ' . $errmsg[$file['error']];
         }

         $f = new self($file['tmp_name']);
         if ($f->isWebImage() === FALSE)
         {
            return 'Invalid Image File Type. Allowed types: GIF, PNG, JPEG';
         }

         if ($f->size > $size) // Image Size
         {
            return 'Invalid Image File Size. Maximum size: ' . ($size / 1024) . 'KB';
         }

         $f->name = $this->_toUTF8($file['name']);

         $funcName[IMAGETYPE_GIF] = 'gif';
         $funcName[IMAGETYPE_JPEG] = 'jpeg';
         $funcName[IMAGETYPE_PNG] = 'png';

         $fileInfo = \getimagesize($f->path);
         $f->type = \image_type_to_extension($fileInfo[2], FALSE);

         $savePath = self::$file_path . '/data/' . $type . '/' . $_SERVER['REQUEST_TIME'] . self::$uid . mt_rand(0, 9) . '.' . $f->type;

         $width = $fileInfo[0];
         $height = $fileInfo[1];
         if ($width > $maxWidth || $height > $maxHeight)
         {
            // resize the image here
            $func = 'imagecreatefrom' . $f->type;
            $image = $func($f->path);

            $w_ratio = $width / $maxWidth; // > 1
            $h_ratio = $height / $maxHeight; // > 1
            if ($w_ratio > $h_ratio)
            {
               $w_new = $maxWidth; // resize based on width
               $h_new = $height / $w_ratio;
            }
            else
            {
               $w_new = $width / $h_ratio;
               $h_new = $maxHeight; // resize based on height
            }

            $image_new = \imagecreatetruecolor($w_new, $h_new);
            \imagecopyresampled($image_new, $image, 0, 0, 0, 0, $w_new, $h_new, $width, $height);
            $func = 'image' . $f->type;
            $func($image_new, $savePath);

            \imagedestroy($image_new);
            \imagedestroy($image);
         }
         else
         {
            // copy image
            $is_saved = \move_uploaded_file($f->path, $savePath);
            if (!$is_saved)
            {
               Log::error('File Save Error: ' . $f->path . ' to ' . $savePath);
               return 'File Save Error: ' . $fileName;
            }
         }

         $f->path = $savePath;
         return $f;
      }
   }
*/
}

?>
