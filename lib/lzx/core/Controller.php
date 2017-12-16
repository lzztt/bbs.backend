<?php declare(strict_types=1);

namespace lzx\core;

use lzx\core\Logger;
use lzx\core\Request;
use lzx\core\Response;
use lzx\core\ResponseReadyException;
use lzx\core\UtilTrait;
use lzx\html\Template;

// only controller will handle all exceptions and local languages
// other classes will report status to controller
// controller set status back the WebApp object
// WebApp object will call Theme to display the content

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

    abstract public function run();

    /**
     * Observer design pattern interfaces
     */
    abstract public function update(Template $html);

    protected function error($msg)
    {
        $this->response->setContent($msg);
        throw new ResponseReadyException();
    }

    protected function pageNotFound()
    {
        $this->response->pageNotFound();
        throw new ResponseReadyException();
    }

    protected function pageForbidden()
    {
        $this->response->pageForbidden();
        throw new ResponseReadyException();
    }

    protected function pageRedirect($uri)
    {
        $this->response->pageRedirect($uri);
        throw new ResponseReadyException();
    }
}
