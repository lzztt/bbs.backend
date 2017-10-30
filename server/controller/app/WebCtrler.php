<?php

namespace site\controller\app;

use site\controller\App;

class WebCtrler extends App
{
    private $name = 'web';

    public function run()
    {
        $this->response->setContent(file_get_contents($this->getLatestVersion($this->name) . '/index.html'));
    }
}

//__END_OF_FILE__
