<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace site\handler\wedding\logout;

use site\handler\wedding\Wedding;

/**
 * Description of Wedding
 *
 * @author ikki
 */
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

//__END_OF_FILE__
