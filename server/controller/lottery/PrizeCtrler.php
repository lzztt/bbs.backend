<?php

namespace site\controller\lottery;

use site\controller\Lottery;
use lzx\html\Template;
use lzx\db\DB;
use site\dbobject\User;

class PrizeCtrler extends Lottery
{

    public function run()
    {
       $this->html->var[ 'content' ] = new Template( 'lotteryPrizes' );
    }

}

//__END_OF_FILE__