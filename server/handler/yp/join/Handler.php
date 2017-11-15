<?php declare(strict_types=1);

namespace site\handler\yp\join;

use site\Controller;
use lzx\html\Template;
use lzx\cache\PageCache;

class Handler extends Controller
{
    public function run()
    {
        $this->cache = new PageCache($this->request->uri);

        $this->var['content'] = new Template('yp_join');
        $this->var['head_title'] = '市场推广 先入为主';
    }
}
