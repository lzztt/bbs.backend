<?php declare(strict_types=1);

namespace lzx\core;

use Exception;
use lzx\core\Request;
use lzx\core\Response;
use lzx\core\Logger;
use lzx\core\JSON;

// service will populate response with JSON data
// handle all exceptions and local languages

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
        throw new Exception();
    }

    protected function forbidden()
    {
        $this->error('forbidden');
    }
}
