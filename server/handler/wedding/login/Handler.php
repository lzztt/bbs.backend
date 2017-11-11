<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace site\handler\wedding\login;

use site\handler\wedding\Wedding;
use lzx\html\Template;

/**
 * Description of Wedding
 *
 * @author ikki
 */
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
