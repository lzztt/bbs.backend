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
      
      $this->_var[ 'content' ] = new Template( 'yp_join' );
      $this->_var[ 'head_title'] = '市场推广 先入为主';
   }

}

//__END_OF_FILE__