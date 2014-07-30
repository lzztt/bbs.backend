<?php

namespace site\controller\yp;

use site\controller\YP;
use lzx\html\Template;
use lzx\cache\PageCache;

class JoinCtrler extends YP
{

   public function run()
   {
      $this->cache = new PageCache( $this->request->uri );
      
      $this->html->var[ 'content' ] = new Template( 'yp_join' );
   }

}

//__END_OF_FILE__