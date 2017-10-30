<?php

namespace site\controller\lottery;

use site\controller\Lottery;
use lzx\db\DB;

class RankCtrler extends Lottery
{
    public function run()
    {
        if ($this->request->uid == self::UID_GUEST) {
            $this->var['content'] = '<div class="messagebox">您需要拥有HoustonBBS帐号，登录后才能开始正式抽奖<br />'
                . '<a class="bigbutton" href="/user/login">登录</a><a class="bigbutton" href="/user/register">注册</a></div>';
            return;
        }

        $db = DB::getInstance();

        if ($this->args[0] === 'record' && is_numeric($this->args[1])) {
            // DB GET round time
        }
        //return new Template( 'lotteryRank', ['userCount' => $userCount, 'recordCount' => $recordCount, 'rank' => $rank] );
    }
}

//__END_OF_FILE__
