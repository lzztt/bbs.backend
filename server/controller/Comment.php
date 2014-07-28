<?php

namespace site\controller;

use site\Controller;
use lzx\core\Request;
use lzx\html\Template;
use site\Config;
use lzx\core\Logger;
use lzx\core\Session;
use lzx\core\Cookie;

abstract class Comment extends Controller
{

   public function __construct( Request $req, Template $html, Config $config, Logger $logger, Session $session, Cookie $cookie )
   {
      parent::__construct( $req, $html, $config, $logger, $session, $cookie );
      
      if ( $this->request->uid == 0 )
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->pageForbidden();
      }
   }

}

//__END_OF_FILE__