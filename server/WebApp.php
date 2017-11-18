<?php declare(strict_types=1);

namespace site;

use Exception;
use lzx\App;
use lzx\core\Handler;
use lzx\db\DB;
use lzx\core\Request;
use lzx\core\Response;
use site\Session;
use lzx\html\Template;
use site\Config;
use site\ControllerFactory;
use lzx\cache\Cache;
use lzx\cache\CacheEvent;
use lzx\cache\CacheHandler;

require_once dirname(__DIR__) . '/lib/lzx/App.php';

class WebApp extends App
{
    protected $config;

    public function __construct()
    {
        parent::__construct();
        // register current namespaces
        $this->loader->registerNamespace(__NAMESPACE__, __DIR__);

        // load configuration
        $this->config = Config::getInstance();
        // display errors on page, turn on debug for DEV stage
        if ($this->config->stage === Config::STAGE_DEVELOPMENT) {
            Handler::$displayError = true;
            DB::$debug = true;
            Template::$debug = true;
        } else {
            Handler::$displayError = false;
            DB::$debug = false;
            Template::$debug = false;
        }

        $this->logger->setDir($this->config->path['log']);
        $this->logger->setEmail($this->config->webmaster);

        // config template
        Template::setLogger($this->logger);
        Template::$path = $this->config->path['theme'];
        Template::$theme = $this->config->theme['roselife'];
        Template::$language = $this->config->language;
    }

    // controller will handle all exceptions and local languages
    // other classes will report status to controller
    // controller set status back the WebApp object
    // WebApp object will call Theme to display the content
    /**
     *
     * @param type $argc
     * @param array $argv
     */
    public function run($argc = 0, array $argv = [])
    {
        $request = Request::getInstance();
        if (!isset($request->language)) {
            $request->language = $this->config->language;
        }

        $getCount = count($request->get);
        if ($getCount) {
            $request->get = array_intersect_key($request->get, array_flip($this->config->getkeys));
            // do not cache page with unsupport get keys
            if (count($request->get) != $getCount) {
                $this->config->cache = false;
            }
        }

        // initialize database connection
        $db = DB::getInstance($this->config->db);

        // config cache
        CacheHandler::$path = $this->config->path['cache'];
        $cacheHandler = CacheHandler::getInstance($db);
        Cache::setHandler($cacheHandler);
        CacheEvent::setHandler($cacheHandler);
        Cache::setLogger($this->logger);

        // initialize session
        $session = Session::getInstance($this->isRobot() ? null : $db);

        // update request uid based on session uid
        $request->uid = (int) $session->getUserID();

        // set user info for logger
        $userinfo = [
            'uid'  => 'https://www.houstonbbs.com/app/user/' . $request->uid,
            'role' => $this->isRobot() ? 'robot' : $session->urole];
        $this->logger->setUserInfo($userinfo);

        $response = Response::getInstance();

        try {
            $ctrler = ControllerFactory::create($request, $response, $this->config, $this->logger, $session);
            $ctrler->run();
        } catch (Exception $e) {
            if ($e->getMessage()) {
                $this->logger->error($e->getMessage(), $e->getTrace());
                $this->logger->flush();
            }
        }

        // send out response
        $response->send();

        // do extra clean up and heavy stuff here
        if ($response->getStatus() < 400) {
            try {
                // flush session
                $session->close();

                // output debug message?
                $debug = ($this->config->stage == Config::STAGE_DEVELOPMENT);

                if ($debug) {
                    $this->logger->info($db->queries);
                }

                // flush database
                $db->flush();

                // controller flush cache
                if ($debug) {
                    $timer = microtime(true);
                    if ($ctrler) {
                        $ctrler->flushCache();
                    }
                    $db->flush();
                    $timer = microtime(true) - $timer;
                    $this->logger->info(sprintf('cache flush time: %8.6f', $timer));
                } else {
                    if ($ctrler) {
                        $ctrler->flushCache();
                    }
                    $db->flush();
                }
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        // flush logger
        $this->logger->flush();
    }

    private function isRobot()
    {
        static $isRobot;

        if (!isset($isRobot)) {
            if ($_SERVER['HTTP_USER_AGENT']) {
                $isRobot = (bool) preg_match('/(http|yahoo|bot|spider)/i', $_SERVER['HTTP_USER_AGENT']);
            } else {
                $isRobot = false;
            }
        }

        return $isRobot;
    }
}
