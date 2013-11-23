<?php

namespace site\controller;

use site\Controller;
use lzx\html\Template;

use site\controller\FindYouFindMe;

class Single extends Controller
{

   public function run()
   {
      $ctr = $this->loadController('FindYouFindMe');
      $ctr->run();
   }

}

//__END_OF_FILE__
