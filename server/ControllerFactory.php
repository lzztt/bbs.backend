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
        $id = null;
        $args = [];
        foreach ($req->getURIargs($req->uri) as $v) {
            if (!is_numeric($v)) {
                $args[] = $v;
            } else {
                // TODO: process all numeric ids for handlers
                if (is_null($id)) {
                    $id = (int) $v;
                }
            }
        }

        if (empty($args)) {
            $args[] = 'home';
        }

        $ctrlerClass = null;
        while ($args && !$ctrlerClass) {
            $ctrler = implode('/', $args);
            $ctrlerClass = static::$route[$ctrler];
            array_pop($args);
        }

        if ($ctrlerClass) {
            $ctrlerObj = new $ctrlerClass($req, $response, $config, $logger, $session);
            $ctrlerObj->args = $args;
            $ctrlerObj->id = $id;
            return $ctrlerObj;
        }

        // cannot find a controller
        $response->pageNotFound();
        throw new \Exception();
    }

    /**
     *
     * @param \lzx\core\Request $req
     * @param \lzx\core\Response $response
     * @param \lzx\core\Logger $logger
     * @param \site\Session $session
     * @return \site\Service
     */
    public static function createService(Request $req, Response $response, Config $config, Logger $logger, Session $session)
    {
        $args = $req->getURIargs($req->uri);
        if (sizeof($args) > 1) {
            $apiClass = static::$route['api/' . $args[1]];
            if ($apiClass) {
                $api = new $apiClass($req, $response, $config, $logger, $session);
                $api->args = array_slice($args, 2);
                return $api;
            }
        }

        // cannot find a service
        $response->pageNotFound();
        throw new \Exception();
    }
}
