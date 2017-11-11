<?php

namespace site\handler\app\ad;

use site\handler\app\App;

class Handler extends App
{
    private $name = 'ad';

    public function run()
    {
        $this->response->setContent(file_get_contents($this->getLatestVersion($this->name) . '/index.html'));
    }
}
