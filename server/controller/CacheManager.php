<?php

namespace site\controller;

use site\Controller;
use lzx\html\Template;

class CacheManager extends Controller
{

   public function run()
   {
      if ($this->request->uid != 1)
      {
         $this->request->pageForbidden();
      }
// logged in user
      else
      {
         parent::run();
         $this->cache->setStatus(FALSE);
         $this->listCache();
      }
   }

   public function listCache()
   {
      $key = $this->request->post['key'];
      $form = '<div><form action="" method="post" accept-charset="UTF-8">Search Cache Key: <input type="text" value="' . $key . '" name="key" /><br />Delete Cache File: <input type="text" name="cache[]" /><br /><input type="submit" value="Submit" /></form></div>';
      $this->html->var['content'] = $form;

      // list file cache
      if ($key)
      {
         $files = glob($this->cache->path . preg_replace('/[^0-9a-z\.\*\_\-]/i', '_', $key) . '*');

         if ($files)
         {
            $files = array_slice($files, 2);
            foreach ($files as $f)
            {
               $f = str_replace($this->cache->path, '', $f);
               $li .= '<li><input type="checkbox" name="cache[]" value="' . $f . '"/>' . $f . '</li>';
            }

            $form = '<div><form action="" method="post" accept-charset="UTF-8"><ol>' . $li . '</ol><input type="submit" value="Delete" /></form></div>';
            $this->html->var['content'] .= $form;
         }
         else
         {
            $this->html->var['content'] .= 'No Cache File Found :(';
         }
      }
      // delete file cache
      if (sizeof($this->request->post['cache']) > 0)
      {
         foreach ($this->request->post['cache'] as $f)
         {
            unlink($this->cache->path . $f);
         }

         $this->html->var['content'] .= '<div>The following cache files have been deleted:<br />' . implode('<br />', $this->request->post['cache']) . '</div>';
      }
   }

}

?>