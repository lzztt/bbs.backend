<?php declare(strict_types=1);

namespace site\handler\wedding;

use site\handler\wedding\Wedding;
use lzx\html\Template;
use lzx\cache\PageCache;

class Handler extends Wedding
{
    public function run()
    {
        $this->cache = new PageCache($this->request->uri);

        $this->var['body'] = new Template('join_form');
    }
}
