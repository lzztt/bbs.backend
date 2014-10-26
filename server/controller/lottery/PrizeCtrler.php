<?php

namespace site\controller\lottery;

use site\controller\Lottery;
use lzx\html\Template;

class PrizeCtrler extends Lottery
{

    public function run()
    {
       $this->_var[ 'content' ] = new Template( 'lotteryPrizes' );
    }

}

//__END_OF_FILE__