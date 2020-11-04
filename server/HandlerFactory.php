<?php

declare(strict_types=1);

namespace site;

use lzx\core\Logger;
use lzx\core\Request;
use lzx\core\Response;
use lzx\exception\NotFound;
use site\Config;
use site\Handler;
use site\Session;
use site\gen\HandlerRouter;

class HandlerFactory
{
    protected static $route = [];

    public static function create(Request $req, Response $resp, Config $config, Logger $logger, Session $session): Handler
    {
        if ($req->isBad()) {
            throw new NotFound();
        }

        list($cls, $args) = self::getHandlerClassAndArgs($req);

        if (!$cls) {
            $cls = HandlerRouter::$route['app'];
            $args = ['default'];
            // throw new NotFound();
        }
        return new $cls($req, $resp, $config, $logger, $session, $args);
    }

    private static function getHandlerClassAndArgs(Request $req): array
    {
        $args = self::getURIargs($req->uri);

        $keys = array_filter($args, function ($value) {
            return !is_numeric($value);
        });

        if (!$keys) {
            $keys[] = 'home';
        }

        $cls = null;
        while ($keys) {
            $key = implode('/', $keys);
            $cls = array_key_exists($key, HandlerRouter::$route) ? HandlerRouter::$route[$key] : null;

            if ($cls) {
                break;
            } else {
                array_pop($keys);
            }
        }

        return [$cls, array_values(array_diff($args, $keys))];
    }

    private static function getURIargs(string $uri): array
    {
        $parts = explode('?', $uri);
        $arg = trim($parts[0], '/');
        return array_values(array_filter(explode('/', $arg), 'strlen'));
    }
}
