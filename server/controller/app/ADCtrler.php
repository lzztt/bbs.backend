<?php

namespace site\controller\app;

use site\controller\App;

class ADCtrler extends App
{
    private $_name = 'ad';

    public function run()
    {
        $this->response->setContent(\file_get_contents($this->_getLatestVersion($this->_name) . '/index.html'));
    }
}

//__END_OF_FILE__
