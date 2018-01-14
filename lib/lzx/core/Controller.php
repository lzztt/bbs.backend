<?php declare(strict_types=1);

namespace lzx\core;

use lzx\core\Logger;
use lzx\core\Request;
use lzx\core\Response;
use lzx\core\ResponseReadyException;
use lzx\core\UtilTrait;
use lzx\html\Template;

abstract class Controller
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

    /**
     * Observer design pattern interfaces
     */
    abstract public function update(Template $html): void;

    protected function error(string $msg): void
    {
        $this->response->setContent($msg);
        throw new ResponseReadyException();
    }

    protected function pageNotFound(): void
    {
        $this->response->pageNotFound();
        throw new ResponseReadyException();
    }

    protected function pageForbidden(): void
    {
        $this->response->pageForbidden();
        throw new ResponseReadyException();
    }

    protected function pageRedirect(string $uri): void
    {
        $this->response->pageRedirect($uri);
        throw new ResponseReadyException();
    }
}
