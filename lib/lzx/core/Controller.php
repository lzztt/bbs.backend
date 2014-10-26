<?php

namespace lzx\core;

use lzx\core\Request;
use lzx\html\Template;
use lzx\core\Logger;
use lzx\core\Session;

// only controller will handle all exceptions and local languages
// other classes will report status to controller
// controller set status back the WebApp object
// WebApp object will call Theme to display the content

/**
 *
 * @property \lzx\core\Logger $logger
 * @property \lzx\core\Response $response
 * @property \lzx\core\Request $request
 * @property \lzx\core\Session $session
 *
 */
abstract class Controller
{

   public $logger;
   public $request;
   public $response;
   public $session;

   public function __construct( Request $req, Response $response, Logger $logger, Session $session )
   {
      $this->request = $req;
      $this->response = $response;
      $this->logger = $logger;
      $this->session = $session;
   }

   abstract public function run();

   /**
    * Observer design pattern interfaces
    */
   abstract public function update( Template $html );

   protected function error( $msg )
   {
      $this->response->setContent( $msg );
      throw new \Exception();
   }

   public function pageNotFound( $msg = NULL )
   {
      $this->response->setContent( $msg );
      $this->response->pageNotFound();
      throw new \Exception();
   }

   public function pageForbidden( $msg = NULL )
   {
      $this->response->setContent( $msg );
      $this->response->pageForbidden();
      throw new \Exception();
   }

   protected function pageRedirect( $uri )
   {
      $this->response->pageRedirect( $uri );
      throw new \Exception();
   }

}

//__END_OF_FILE__
