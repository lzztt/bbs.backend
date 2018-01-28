<?php declare(strict_types=1);

namespace site\handler\comment;

use lzx\core\Logger;
use lzx\core\Request;
use lzx\core\Response;
use lzx\exception\Forbidden;
use site\Config;
use site\Controller;
use site\Session;

abstract class Comment extends Controller
{
    public function __construct(Request $req, Response $response, Config $config, Logger $logger, Session $session, array $args)
    {
        parent::__construct($req, $response, $config, $logger, $session, $args);

        if ($this->request->uid == 0) {
            $this->logger->warn('wrong action : uid = ' . $this->request->uid);
            throw new Forbidden();
        }
    }
}
