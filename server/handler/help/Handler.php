<?php declare(strict_types=1);

namespace site\handler\help;

use site\Controller;
use lzx\html\Template;

class Handler extends Controller
{
    public function run()
    {
         $this->var['content'] = new Template('help');
    }
}
