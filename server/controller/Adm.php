<?php

namespace site\controller;

use site\Controller;
use lzx\core\Request;
use lzx\core\Response;
use lzx\html\Template;
use site\Config;
use lzx\core\Logger;
use site\Session;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of adm
 *
 * @author ikki
 */
abstract class Adm extends Controller
{
    public function __construct(Request $req, Response $response, Config $config, Logger $logger, Session $session)
    {
        parent::__construct($req, $response, $config, $logger, $session);

        if ($this->request->uid !== self::UID_ADMIN) {
            $this->pageNotFound();
        }

        Template::$theme = $this->config->theme['adm'];
    }
}

//__END_OF_FILE__
