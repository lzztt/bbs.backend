<?php declare(strict_types=1);

namespace lzx\core;

use lzx\core\ResponseException;
use lzx\core\Request;
use lzx\core\Response;
use lzx\core\Logger;
use lzx\core\UtilTrait;

// service will populate response with JSON data
// handle all exceptions and local languages

abstract class Service
{
    use UtilTrait;

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
        $this->response->setContent($return ? $return : (object) null);
    }

    protected function error($msg)
    {
        $this->json(['error' => $msg]);
        throw new ResponseException();
    }

    protected function forbidden()
    {
        $this->error('forbidden');
    }
}
