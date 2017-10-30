<?php

namespace site\controller\app;

use site\controller\App;

class CacheCtrler extends App
{
    private $name = 'cache';

    public function run()
    {
        $this->response->setContent(file_get_contents($this->getLatestVersion($this->name) . '/index.html'));
    }
}

//__END_OF_FILE__
