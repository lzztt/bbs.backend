<?php

namespace site\controller;

use lzx\core\Controller;
use lzx\core\BBCode;
use lzx\html\HTMLElement;
use lzx\html\Template;
use site\dataobject\Node as NodeObject;
use site\dataobject\NodeYellowPage;
use site\dataobject\Comment;
use site\dataobject\File;
use site\dataobject\User;
use site\dataobject\Activity;

class MultiPost extends Controller
{

   public function run()
   {

      $len = \mb_strlen($body);
      if ($len > 50)
      {
         //
         for ($i = 1; $i < 10; $i = $i + 2)
         {
            $start = \intval($len * $i / 10 - 2);
            $sub = \mb_substr($body, $start, 5);
         }
      }
   }

}

//__END_OF_FILE__
