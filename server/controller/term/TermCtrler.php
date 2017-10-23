<?php

namespace site\controller\term;

use site\controller\Term;
use lzx\html\Template;
use lzx\cache\PageCache;

class TermCtrler extends Term
{
    public function run()
    {
        $this->cache = new PageCache($this->request->uri);

        $sitename = [
            'site_zh_cn' => '缤纷' . self::$_city->name . '华人网',
            'site_en_us' => \ucfirst(self::$_city->uriName) . 'BBS.com'
        ];

        $this->_var['content'] = new Template('term', $sitename);
    }
}

//__END_OF_FILE__
