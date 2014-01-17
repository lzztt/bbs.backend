<?php

namespace site\controller;

use site\Controller;
use lzx\html\Template;

class Term extends Controller
{

   public function run()
   {
      parent::run();

      $sitename = [
         'site_zh_cn' => '缤纷休斯顿华人网',
         'site_en_us' => 'HoustonBBS.com'
      ];

      $this->html->var['content'] = new Template( 'term', $sitename );
   }

}

//__END_OF_FILE__
