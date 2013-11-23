<?php

namespace site\controller;

use site\Controller;
use site\dataobject\Image;

class File extends Controller
{

   public function run()
   {
      $this->checkAJAX();
      // just exit on other request
      $this->request->pageExit();
   }

   public function ajax()
   {
      // uri = /file/ajax/upload
      $action = $this->request->args[2];
      return $this->runAction($action);
   }

   public function uploadAction()
   {
      if ($this->request->uid == 0) // we simply don't allow guest to post this form
      {
         $res = 'upload_err_permission_denied';
      }
      elseif (empty($this->request->files))
      {
         $res = 'upload_err_no_file';
      }
      else
      {
         $fobj = new Image();
         $res = $fobj->saveFile($this->request->files, $this->path['file'], $this->request->timestamp, $this->request->uid);
      }

      if (\is_string($res))
      {
         $res = array('error' => $this->l($res));
      }
      elseif (\is_array($res))
      {
         foreach ($res['error'] as $i => $f)
         {
            $res['error'][$i]['error'] = $this->l($f['error']);
         }
      }

      return $res;
   }

   public function deleteAction()
   {
      // if has fid, delete from database
      // delete from file system
   }

}

//__END_OF_FILE__
