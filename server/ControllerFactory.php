<?php

namespace site;

use lzx\core\Request;
use lzx\core\Response;
use site\Config;
use lzx\core\Logger;
use site\Session;

/**
 * Description of ControllerFactory
 *
 * @author ikki
 * use latest static binding
 */
class ControllerFactory
{
    protected static $route = [];

    /**
     *
     * @param \lzx\core\Request $req
     * @param \lzx\core\Response $response
     * @param \site\Config $config
     * @param \lzx\core\Logger $logger
     * @param \site\Session $session
     * @return \site\Controller
     */
    public static function createController(Request $req, Response $response, Config $config, Logger $logger, Session $session)
    {
        $args = $req->getURIargs($req->uri);

        $keys = array_filter($args, function ($value) {
            return !is_numeric($value);
        });

        if (empty($keys)) {
            $keys[] = 'home';
        }

        $cls = null;
        while ($keys) {
            $key = implode('/', $keys);
            $cls = static::$route[$key];
            
            if ($cls) {
                break;
            } else {
                array_pop($keys);
            }
        }

        if ($cls) {
            $handler = new $cls($req, $response, $config, $logger, $session);
            $handler->args = array_values(array_diff($args, $keys));
            return $handler;
        } else {
            // cannot find a controller
            $response->pageNotFound();
            throw new \Exception();
        }
    }
}
