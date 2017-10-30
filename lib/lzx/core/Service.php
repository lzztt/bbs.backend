<?php

namespace lzx\core;

use lzx\core\Request;
use lzx\core\Response;
use lzx\core\Logger;
use lzx\core\JSON;

// service will populate response with JSON data
// handle all exceptions and local languages

/**
 *
 * @property \lzx\core\Logger $logger
 * @property \lzx\core\Response $response
 * @property \lzx\core\Request $request
 *
 */
abstract class Service
{
    public $logger;
    public $request;
    public $response;

    public function __construct(Request $req, Response $response, Logger $logger)
    {
        $this->request = $req;
        $this->response = $response;
        $this->logger = $logger;
    }

    protected function json(array $return = null)
    {
        $this->response->type = Response::JSON;
        $this->response->setContent(new JSON($return));
    }

    protected function error($msg)
    {
        $this->json(['error' => $msg]);
        throw new \Exception();
    }

    protected function forbidden()
    {
        $this->error('forbidden');
    }
}

//__END_OF_FILE__
