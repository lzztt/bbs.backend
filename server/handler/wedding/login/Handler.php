<?php declare(strict_types=1);

namespace site\handler\wedding\login;

use site\handler\wedding\Wedding;
use lzx\html\Template;

class Handler extends Wedding
{
    public function run()
    {
        Template::$theme = $this->config->theme['wedding2'];

        $defaultRedirect = '/wedding/listall';

        if ($this->request->post) {
            if ($this->request->post['password'] == 'alexmika') {
                $this->session->loginStatus = true;
                $uri = $this->session->loginRedirect;
                unset($this->session->loginRedirect);
                $this->pageRedirect($uri ? $uri : $defaultRedirect);
            }
        }

        $this->displayLogin();
    }
}
