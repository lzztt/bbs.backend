<?php declare(strict_types=1);

namespace site;

use Exception;
use lzx\App;
use lzx\cache\Cache;
use lzx\cache\CacheEvent;
use lzx\cache\CacheHandler;
use lzx\core\Request;
use lzx\core\Response;
use lzx\core\UtilTrait;
use lzx\db\DB;
use lzx\html\Template;
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
        $this->debug = $this->config->stage === Config::STAGE_DEVELOPMENT;
        if ($this->debug) {
            DB::$debug = true;
            Template::$debug = true;
        } else {
            DB::$debug = false;
            Template::$debug = false;
        }

        $this->logger->setFile($this->config->path['log'] . '/' . $this->config->domain . '.log');
        $this->logger->setEmail($this->config->webmaster, 'Error: ' . $_SERVER['REQUEST_URI'], 'logger@' . $this->config->domain);

        Template::setLogger($this->logger);
        Template::$path = $this->config->path['theme'];
        Template::$theme = $this->config->theme;
    }

    public function run(array $args = []): void
    {
        $request = Request::getInstance();

        $db = DB::getInstance($this->config->db);
        $this->setupCache($db);
        $session = Session::getInstance(!$request->isRobot);
        $request->uid = $session->get('uid');

        $this->logger->addExtraInfo([
            'user' => 'https://www.houstonbbs.com/app/user/' . $request->uid,
            'ip' => $request->ip,
            'city' => self::getLocationFromIp($request->ip),
        ]);

        $response = Response::getInstance();

        try {
            $ctrler = HandlerFactory::create($request, $response, $this->config, $this->logger, $session);
            $ctrler->run();
        } catch (Exception $e) {
            $response->handleException($e);
        }

        $response->send();

        if (!$request->isRobot && $response->getStatus() < 400) {
            $this->flush($session, $db, $ctrler);
        }
    }

    private function setupCache(DB $db): void
    {
        CacheHandler::$path = $this->config->path['cache'];
        $cacheHandler = CacheHandler::getInstance($db);
        Cache::setHandler($cacheHandler);
        CacheEvent::setHandler($cacheHandler);
        Cache::setLogger($this->logger);
    }

    private function flush(Session $session, DB $db, $ctrler): void
    {
        $session->close();

        if ($this->debug) {
            $this->logger->info($db->queries);
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
