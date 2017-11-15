<?php declare(strict_types=1);

namespace site\handler\comment;

use site\Controller;
use lzx\core\Request;
use lzx\core\Response;
use site\Config;
use lzx\core\Logger;
use site\Session;

abstract class Comment extends Controller
{
    public function __construct(Request $req, Response $response, Config $config, Logger $logger, Session $session)
    {
        parent::__construct($req, $response, $config, $logger, $session);

        if ($this->request->uid == 0) {
            $this->logger->warn('wrong action : uid = ' . $this->request->uid);
            $this->pageForbidden();
        }
    }
}
