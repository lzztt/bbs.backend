<?php

namespace lzx\core;

use lzx\core\Request;
use lzx\core\Response;
use lzx\core\Logger;
use lzx\core\Session;
use lzx\core\JSON;

// service will populate response with JSON data
// handle all exceptions and local languages

/**
 *
 * @property \lzx\core\Logger $logger
 * @property \lzx\core\Response $response
 * @property \lzx\core\Request $request
 * @property \lzx\core\Session $session
 *
 */
abstract class Service
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

   protected function _json( array $return )
   {
      $this->response->type = Response::JSON;
      $this->response->setContent( new JSON( $return ) );
   }

   protected function error( $msg )
   {
      $this->_json( [ 'error' => $msg ] );
      throw new \Exception();
   }

   protected function forbidden()
   {
      $this->error( 'forbidden' );
   }

}

//__END_OF_FILE__
