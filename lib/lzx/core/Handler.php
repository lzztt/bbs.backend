<?php declare(strict_types=1);

namespace lzx\core;

use lzx\core\Logger;
use lzx\core\Request;
use lzx\core\Response;
use lzx\core\UtilTrait;

abstract class Handler
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

    abstract public function run(): void;

    protected function json(array $return = null): void
    {
        $this->response->setContent($return ? $return : (object) null);
    }
}
