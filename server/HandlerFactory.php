<?php declare(strict_types=1);

namespace site;

use lzx\core\Handler;
use lzx\core\Logger;
use lzx\core\Request;
use lzx\core\Response;
use lzx\exception\NotFound;
use site\Config;
use site\HandlerRouter;
use site\Session;

class HandlerFactory
{
    protected static $route = [];

    public static function create(Request $req, Response $resp, Config $config, Logger $logger, Session $session): Handler
    {
        if (strpos($req->uri, Request::QUERY_INVALID_CHAR) !== false) {
            throw new NotFound();
        }

        list($cls, $args) = self::getHandlerClassAndArgs($req);

        if (!$cls) {
            throw new NotFound();
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
            $cls = HandlerRouter::$route[$key];

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
