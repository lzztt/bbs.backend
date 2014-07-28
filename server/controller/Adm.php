<?php

namespace site\controller;

use site\Controller;
use lzx\core\Request;
use lzx\html\Template;
use site\Config;
use lzx\core\Logger;
use lzx\core\Session;
use lzx\core\Cookie;

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

   public function __construct( Request $req, Template $html, Config $config, Logger $logger, Session $session, Cookie $cookie )
   {
      parent::__construct( $req, $html, $config, $logger, $session, $cookie );

      if ( $this->request->uid !== self::ADMIN_UID )
      {
         $this->pageNotFound();
      }

      Template::$theme = $this->config->theme[ 'adm' ];
   }

}

//__END_OF_FILE__