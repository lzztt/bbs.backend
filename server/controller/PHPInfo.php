<?php

namespace site\controller;

use site\Controller;

class PHPInfo extends Controller
{

   public function run()
   {
      if ($this->request->uid !== 126 && $this->request->uid !== 1)
      {
         $this->request->pageNotFound();
      }
      phpinfo();
   }

}

//__END_OF_FILE__
