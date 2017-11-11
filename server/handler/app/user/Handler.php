<?php

namespace site\handler\app\user;

use site\handler\app\App;

class Handler extends App
{
    private $name = 'user';

    public function run()
    {
        $this->response->setContent(file_get_contents($this->getLatestVersion($this->name) . '/index.html'));
    }
}

//__END_OF_FILE__
