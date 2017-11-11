<?php

namespace site\handler\yp\join;

use site\handler\yp\YP;
use lzx\html\Template;
use lzx\cache\PageCache;

class Handler extends YP
{
    public function run()
    {
        $this->cache = new PageCache($this->request->uri);

        $this->var['content'] = new Template('yp_join');
        $this->var['head_title'] = '市场推广 先入为主';
    }
}
