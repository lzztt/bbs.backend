<?php declare(strict_types=1);

namespace site\handler\single\login;

use site\handler\single\Single;

class Handler extends Single
{
    public function run()
    {
        $defaultRedirect = '/single/attendee';

        if ($this->request->post) {
            if ($this->request->post['password'] == 'alexmika6630') {
                $this->session->loginStatus = true;
                $uri = $this->session->loginRedirect;
                unset($this->session->loginRedirect);
                $this->pageRedirect($uri ? $uri : $defaultRedirect);
            }
        }

        $this->displayLogin();
    }
}
