<?php

namespace site\handler\single\logout;

use site\handler\single\Single;

class Handler extends Single
{
    public function run()
    {
        $defaultRedirect = '/single/attendee';

        unset($this->session->loginStatus);
        if ($this->request->referer && $this->request->referer !== '/single/logout') {
            $uri = $this->request->referer;
        } else {
            $uri = $defaultRedirect;
        }
        $this->pageRedirect($uri);
    }
}
