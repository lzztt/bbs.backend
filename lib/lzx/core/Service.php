<?php

namespace lzx\core;

use lzx\core\Request;
use lzx\core\Response;
use lzx\core\Logger;
use lzx\core\Session;

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

   protected function _json( $return )
   {
      $json = \json_encode( $return, \JSON_NUMERIC_CHECK | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE );
      if ( $json === FALSE )
      {
         $json = '{"error":"json encode error"}';
      }
      $this->response->type = Response::JSON;
      $this->response->setContent( $json );
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
