<?php

namespace site\controller\term;

use site\controller\Term;
use lzx\html\Template;
use lzx\cache\PageCache;

class TermCtrler extends Term
{

   public function run()
   {
      $this->cache = new PageCache( $this->request->uri );
      
      $sitename = [
         'site_zh_cn' => '缤纷休斯顿华人网',
         'site_en_us' => 'HoustonBBS.com'
      ];

      $this->html->var['content'] = new Template( 'term', $sitename );
   }

}

//__END_OF_FILE__
