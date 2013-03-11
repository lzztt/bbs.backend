<?php

namespace site\controller;

use lzx\core\Controller;
use lzx\html\Template;

class Term extends Controller
{

   public function run()
   {
      $page = $this->loadController('Page');
      $page->updateInfo();
      $page->setPage();

      $sitename = array(
         'site_zh_cn' => '缤纷休斯顿华人网',
         'site_en_us' => 'HoustonBBS.com'
      );

      $this->html->var['content'] = new Template('term', $sitename);
   }

}

//__END_OF_FILE__
