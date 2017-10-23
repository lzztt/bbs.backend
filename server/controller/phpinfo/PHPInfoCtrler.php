<?php

namespace site\controller\phpinfo;

use site\controller\PHPInfo;

class PHPInfoCtrler extends PHPInfo
{
    public function run()
    {
        if ($this->request->uid !== 126 && $this->request->uid !== 1) {
            $this->pageNotFound();
        }
        \phpinfo();
    }
}

//__END_OF_FILE__
