<?php

namespace site\controller\yp;

use site\controller\YP;
use lzx\html\Template;

class JoinCtrler extends YP
{

   public function run()
   {
      $this->html->var[ 'content' ] = new Template( 'yp_join' );
   }

}

//__END_OF_FILE__