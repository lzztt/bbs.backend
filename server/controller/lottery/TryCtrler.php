<?php

namespace site\controller\lottery;

use site\controller\Lottery;
use lzx\html\Template;

class TryCtrler extends Lottery
{

    public function run()
    {
        $lottery = is_array( $this->session->lottery ) ? $this->session->lottery : [];

        if ( $this->args[0] === 'run' )
        {
            if ( isset( $lottery[$this->request->timestamp] ) )
            {
                die( 'Please Slow Down<br /><a href="' . $this->request->referer . '">go back</a>' );
            }
            $lottery[$this->request->timestamp] = \mt_rand( 0, 100 );
            $this->session->lottery = $lottery;
            $this->request->redirect( $this->request->referer );
        }
        if ( $this->args[0] === 'clear' )
        {
            unset( $this->session->lottery );
            $this->request->redirect( $this->request->referer );
        }

        \krsort( $lottery );

        $this->html->var['content'] = new Template( 'lotteryTry', ['lottery' => $lottery] );
    }

}

//__END_OF_FILE__