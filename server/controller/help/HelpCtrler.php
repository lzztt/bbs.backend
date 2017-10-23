<?php

namespace site\controller\help;

use site\controller\Help;
use lzx\html\Template;

class HelpCtrler extends Help
{
    public function run()
    {
         $this->_var['content'] = new Template('help');
    }
}

//__END_OF_FILE__
