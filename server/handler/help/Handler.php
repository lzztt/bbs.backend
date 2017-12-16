<?php declare(strict_types=1);

namespace site\handler\help;

use lzx\html\Template;
use site\Controller;

class Handler extends Controller
{
    public function run()
    {
         $this->var['content'] = new Template('help');
    }
}
