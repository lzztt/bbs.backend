<?php declare(strict_types=1);

namespace site;

use Exception;
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
        if ($this->config->city->domain === 'bayever.com') {
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
        $request = Request::getInstance();

        $db = DB::getInstance($this->config->db);
        $this->setupCache($db);
        $session = Session::getInstance(!$request->isRobot());
        $request->uid = $session->get('uid');

        $this->logger->addExtraInfo([
            'user' => 'https://www.' . $this->config->domain . '/app/user/' . $request->uid,
            'ip' => $request->ip,
            'city' => self::getLocationFromIp($request->ip),
            'agent' => $request->agent,
            'referer' => $request->referer,
            'data' => $request->data,
        ]);

        $response = Response::getInstance();

        try {
            $ctrler = HandlerFactory::create($request, $response, $this->config, $this->logger, $session);
            $ctrler->run();
            $ctrler->afterRun();
        } catch (Exception $e) {
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
