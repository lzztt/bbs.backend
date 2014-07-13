<?php

namespace site\controller;

use site\Controller;
use lzx\core\Request;
use lzx\html\Template;
use site\Config;
use lzx\core\Logger;
use lzx\core\Cache;
use lzx\core\Session;
use lzx\core\Cookie;

abstract class PM extends Controller
{

   const PMS_PER_PAGE = 25;

   public function __construct( Request $req, Template $html, Config $config, Logger $logger, Cache $cache, Session $session, Cookie $cookie )
   {
      parent::__construct( $req, $html, $config, $logger, $cache, $session, $cookie );
      // don't cache user page at page level
      $this->cache->setStatus( FALSE );
      if ( $this->request->uid == self::GUEST_UID )
      {
         $this->_dispayLogin( $this->request->uri );
      }
   }

}

//__END_OF_FILE__
