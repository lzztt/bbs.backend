<?php

namespace site\controller\app;

use site\controller\App;

class WebCtrler extends App
{
    private $_name = 'web';

    public function run()
    {
        $this->response->setContent(\file_get_contents($this->_getLatestVersion($this->_name) . '/index.html'));
    }
}

//__END_OF_FILE__
