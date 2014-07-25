<?php

namespace lzx\core;

use lzx\core\Request;
use lzx\html\Template;
use lzx\core\Logger;
use lzx\core\Session;
use lzx\core\Cookie;

// only controller will handle all exceptions and local languages
// other classes will report status to controller
// controller set status back the WebApp object
// WebApp object will call Theme to display the content

/**
 *
 * @property \lzx\Core\Cache $cache
 * @property \lzx\core\Logger $logger
 * @property \lzx\html\Template $html
 * @property \lzx\core\Request $request
 * @property \lzx\core\Session $session
 * @property \lzx\core\Cookie $cookie
 *
 */
abstract class Controller implements \SplObserver
{

   protected static $l = [ ];
   public $logger;
   public $html;
   public $request;
   public $session;
   public $cookie;
   protected $class;

   public function __construct(Request $req, Template $html, Logger $logger, Session $session, Cookie $cookie)
   {
      $this->class = \get_class( $this );
      $this->request = $req;
      $this->html = $html;
      $this->logger = $logger;
      $this->session = $session;
      $this->cookie = $cookie;
   }

   abstract public function run();

   protected function error( $msg, $log = FALSE )
   {
      Cache::$status = FALSE;
      if ( $log )
      {
         $this->logger->error( $msg );
      }
      $this->html->error( $msg );
      $this->request->pageExit( (string) $this->html );
   }

}

//__END_OF_FILE__
