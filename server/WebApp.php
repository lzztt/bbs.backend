<?php

declare(strict_types=1);

namespace site;

use Exception;
use Laminas\Diactoros\ServerRequestFactory;
use lzx\App;
use lzx\cache\CacheHandler;
use lzx\core\Request;
use lzx\core\Response;
use lzx\core\UtilTrait;
use lzx\db\DB;
use site\Config;
use site\HandlerFactory;
use site\Session;

class WebApp extends App
{
    use UtilTrait;

    protected $config;
    private $debug;

    public function __construct()
    {
        parent::__construct();

        $this->config = Config::getInstance();
        if ($this->config->city->id === City::SFBAY) {
            date_default_timezone_set('America/Los_Angeles');
        } else {
            date_default_timezone_set('America/Chicago');
        }
        $this->debug = $this->config->mode === Config::MODE_DEV;
        DB::$debug = $this->debug;

        $this->logger->setFile($this->config->path['log'] . '/' . $this->config->domain . '.log');
        $this->logger->setEmail($this->config->webmaster, 'Error: ' . $_SERVER['REQUEST_URI'], 'logger@' . $this->config->domain);
    }

    public function run(array $args): void
    {
        $request = new Request(ServerRequestFactory::fromGlobals());

        $db = DB::getInstance($this->config->db);
        $this->setupCache();
        $session = new Session(!$request->isRobot());
        $uid = $session->get('uid');

        $this->logger->addContext([
            'user' => $uid > 0
                ? 'https://www.' . $this->config->domain . '/app/user/' . $uid
                : ($request->isRobot() ? 'ROBOT' : 'GUEST'),
            'ip' => $request->ip,
            'city' => self::getLocationFromIp($request->ip),
            'agent' => $request->agent,
            'referer' => $request->referer,
            'data' => $request->data,
        ]);

        $response = new Response();
        $route = 'unknown';
        $role = $request->isRobot() ? 'robot' : ($uid > 0 ? 'user' : 'guest');
        $metric = new Metric();
        try {
            $ctrler = HandlerFactory::create($request, $response, $this->config, $this->logger, $session);

            $route = str_replace(['site\\handler\\', '\\Handler', '\\'], ['', '', '/'], get_class($ctrler));
            $counter = $metric->getOrRegisterCounter('hbbs', 'route_count', 'route counter', ['route', 'role']);
            $counter->inc([$route, $role]);

            $ctrler->beforeRun();
            $ctrler->run();
            $ctrler->afterRun();
        } catch (Exception $e) {
            $counter = $metric->getOrRegisterCounter('hbbs', 'route_exception_count', 'route exception counter', ['route', 'role', 'type']);
            $class = get_class($e);
            $pos = strrpos($class, '\\');
            $type = $pos !== false ? substr($class, $pos + 1) : $class;
            $counter->inc([$route, $role, $type]);

            $response->handleException($e);
        }

        $response->send();

        if (!$request->isBad() && !$request->isRobot() && $response->getStatus() < 400) {
            $this->flush($session, $db, $ctrler);
        }
    }

    private function setupCache(): void
    {
        $cacheHandler = CacheHandler::getInstance();
        $cacheHandler->setLogger($this->logger);
        $cacheHandler->setPath($this->config->path['cache']);
    }

    private function flush(Session $session, DB $db, $ctrler): void
    {
        $session->close();

        if ($this->debug) {
            $this->logger->info(implode(' ', $db->queries));
        }

        $db->flush();

        if ($ctrler) {
            if ($this->debug) {
                $timer = microtime(true);
                $ctrler->flushCache();
                $db->flush();
                $this->logger->info(sprintf('cache flush time: %8.6f', microtime(true) - $timer));
            } else {
                $ctrler->flushCache();
                $db->flush();
            }
        }

        $this->logger->flush();
    }
}
