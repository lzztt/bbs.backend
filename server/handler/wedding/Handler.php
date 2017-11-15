<?php declare(strict_types=1);

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace site\handler\wedding;

use site\handler\wedding\Wedding;
use lzx\html\Template;
use lzx\cache\PageCache;

/**
 * Description of Wedding
 *
 * @author ikki
 */
class Handler extends Wedding
{
    public function run()
    {
        $this->cache = new PageCache($this->request->uri);

        $this->var['body'] = new Template('join_form');
    }
}
