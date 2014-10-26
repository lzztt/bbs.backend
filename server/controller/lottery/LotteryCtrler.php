<?php

namespace site\controller\lottery;

use site\controller\Lottery;
use lzx\html\Template;

class LotteryCtrler extends Lottery
{

   public function run()
   {
      $this->_var[ 'content' ] = new Template( 'lotteryRules' );
   }

}

//__END_OF_FILE__