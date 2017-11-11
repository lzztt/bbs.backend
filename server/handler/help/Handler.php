<?php

namespace site\handler\help;

use site\handler\help\Help;
use lzx\html\Template;

class Handler extends Help
{
    public function run()
    {
         $this->var['content'] = new Template('help');
    }
}

//__END_OF_FILE__
