<?php declare(strict_types=1);

namespace site\handler\wedding\logout;

use site\handler\wedding\Wedding;

class Handler extends Wedding
{
    public function run()
    {
        $defaultRedirect = '/wedding/listall';

        unset($this->session->loginStatus);
        if ($this->request->referer && $this->request->referer !== '/wedding/logout') {
            $uri = $this->request->referer;
        } else {
            $uri = $defaultRedirect;
        }
        $this->pageRedirect($uri);
    }
}
