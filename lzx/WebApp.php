<?php

namespace lzx;

use lzx\core\ClassLoader;
use lzx\core\Handler;
use lzx\core\Config;
use lzx\core\Logger;
use lzx\core\MySQL;
use lzx\core\Request;
use lzx\core\Session;
use lzx\core\Cookie;
use lzx\core\Cache;
use lzx\core\Controller;
use lzx\html\Template;
use lzx\core\File;

/**
 *
 * @property lzx\core\Config $config
 * @property lzx\core\Logger $logger
 * @property lzx\core\MySQL $db
 * @property lzx\core\Cache $cache
 * @property lzx\core\Request $request
 * @property lzx\core\Session $session
 */
class WebApp
{

   private $siteNamespace;
   private $config;
   private $logger;
   private $path;

   public function __construct($siteNamespace, $siteDir)
   {
      try
      {
         if (\mb_internal_encoding("UTF-8") === FALSE)
         {
            throw new \Exception('failed to set utf-8 encoding');
         }

         if (\is_dir($siteDir) && \is_readable($siteDir))
         {
            $this->path = array(
               'root' => $siteDir,
               'log' => $siteDir . '/logs',
               'file' => $siteDir . '/static',
               'language' => $siteDir . '/languages',
               'theme' => $siteDir . '/themes',
            );
         }
         else
         {
            throw new \Exception('siteDir is not accessible');
         }

         $_file = __DIR__ . '/core/ClassLoader.php';
         if (\is_file($_file) && \is_readable($_file))
         {
            require_once $_file;
         }
         else
         {
            throw new \Exception('cannot load autoloader class');
         }
         $loader = ClassLoader::getInstance();
         $loader->registerNamespace(__NAMESPACE__, __DIR__);
         $loader->registerNamespace($siteNamespace, $siteDir);

         // set ErrorHandler, convert error to ErrorException
         Handler::setErrorHandler();

         $this->logger = Logger::getInstance($this->path['log'], array(), TRUE);
         $this->logger->userAgent = $_GET['umode'];

         // set logger before set the Exception Handler
         Handler::$logger = $this->logger;
         // set ExceptionHandler
         Handler::setExceptionHandler();

         // load site config and class config
         $this->config = Config::getInstance($siteDir . '/config.php');
         if (\is_null($this->config->cache))
         {
            // enable cache if cache not set and not in developemtn stage
            $this->config->cache = ($this->config->stage !== 'development');
         }

         // display errors on page if not in production stage
         Handler::$showErrorOnPage = ($this->config->stage !== 'production');
      }
      catch (\Exception $e)
      {
         $msg = '[longzox] initialization exception: [' . $type . '] ' . $e->getMessage() . \PHP_EOL . $e->getTraceAsString();
         if ($this->logger instanceof Logger)
         {
            $this->logger->error($msg);
         }
         else
         {
            \error_log($msg);
         }
         exit('longzox framework WebApp initialization error: ' . $e->getMessage());
      }

      // website is offline
      if ($this->config->offline)
      {
         $offline_file = $this->path['file'] . '/offline.html';
         $output = \is_file($offline_file) ? \file_get_contents($offline_file) : 'website is currently offline';
         // page exit
         \header('Content-Type: text/html; charset=UTF-8');
         exit($output);
      }

      $this->site_namespace = $siteNamespace;
   }

   public function run()
   {
      // only controller will handle all exceptions and local languages
      // other classes will report status to controller
      // controller set status back the WebApp object
      // WebApp object will call Theme to display the content

      $request = $this->getRequest();

      if ($this->validateRequest($request) === FALSE)
      {
         $request->pageNotFound();
      }

      $db = MySQL::getInstance($this->config->database, TRUE);
      $db->setLogger($this->logger);
      if ($this->config->stage !== 'production') // DEV or TEST stage
      {
         $db->debugMode = TRUE;
      }

      // get cookie
      $cookie = $this->getCookie();
      if ($cookie->uid == 0 && isset($cookie->urole))
      {
         unset($cookie->urole);
      }

      // start session
      if ($request->umode == Controller::UMODE_ROBOT)
      {
         $session = new \stdClass();
         $session->uid = 0;
      }
      else
      {
         $session = $this->startSession();
         if ($cookie->uid != $session->uid)
         {
            $cookie->clear();
            $session->clear();
         }
      }

      // set request uid based on session uid
      $request->uid = $session->uid;

      // start cache
      $cache = Cache::getInstance($this->config->cache_path, $request->umode, $cookie->urole);
      $cache->setLogger($this->logger);
      $cache->setStatus($this->config->cache);

      $this->registerHookEventListener();

      // start template
      Template::$theme = $this->config->theme;
      Template::$path = $this->path['theme'] . '/' . $this->config->theme . '/' . $request->umode;
      $html = new Template('html');
      $html->var['domain'] = $this->config->domain;

      try
      {
         $ctrler = $this->getController($request);
      }
      catch (\Exception $e)
      {
         $request->pageNotFound($e->getMessage());
      }

      $ctrler->path = $this->path;
      $ctrler->logger = $this->logger;
      $ctrler->cache = $cache;
      $ctrler->html = $html;
      $ctrler->request = $request;
      $ctrler->session = $session;
      $ctrler->cookie = $cookie;
      $ctrler->run();

      $html = (string) $html;

      // output page content
      \header('Content-Type: text/html; charset=UTF-8');
      echo $html;
      \flush();

      if (Template::getStatus() === TRUE)
      {
         $cache->storePage($html);
      }

      if ($this->config->stage !== 'production') // DEV or TEST stage
      {
         echo '<pre>' . $request->datetime . \PHP_EOL . $this->config->stage . \PHP_EOL;
         echo \print_r(MySQL::$queries, TRUE) . '</pre>';
      }
   }

   public function validateRequest(Request $request) // we don't ban IPs
   {
      // ban! invalid $_GET[]
      if ($this->config->get_keys)
      {
         $get_keys = \explode(',', $this->config->get_keys);
         if (\sizeof($get_keys) > 0)
         {
            foreach (\array_keys($request->get) as $key)
            {
               if (!\in_array($key, $get_keys) && $key !== 'umode')
               {
                  $this->logger->error('unsupport GET key:' . $key);
                  return FALSE;
               }
            }
         }
      }

      // ban! bad robot access
      if ($this->config->robot_controllers)
      {
         $robot_controllers = \explode(',', $this->config->robot_controllers);
         if (\sizeof($robot_controllers) > 0)
         {
            if ($request->umode == Controller::UMODE_ROBOT && !\in_array($request->args[0], $robot_controllers))
            {
               $this->logger->error('unsupport robot controller:' . $request->args[0]);
               return FALSE;
            }
         }
      }

      //valid request
      return TRUE;
   }

   /**
    *
    * @return Request
    */
   public function getRequest()
   {
      $req = Request::getInstance();
      if (!\in_array($req->umode, array(Controller::UMODE_PC, Controller::UMODE_MOBILE, Controller::UMODE_ROBOT)))
      {
         $req->umode = Controller::UMODE_PC;
      }
      if (!isset($req->language))
      {
         $req->language = $this->config->language;
      }
      return $req;
   }

   /**
    *
    * @return Session
    */
   public function startSession()
   {
      $lifetime = $this->config->cookie->lifetime;
      $path = $this->config->cookie->path ? $this->config->cookie->path : '/';
      $domain = $this->config->cookie->domain ? $this->config->cookie->domain : $this->config->domain;
      \session_set_cookie_params($lifetime, $path, $domain);
      \session_name('LZXSID');

      $session = Session::getInstance();

      return $session;
   }

   public function getCookie()
   {
      $lifetime = $this->config->cookie->lifetime;
      $path = $this->config->cookie->path ? $this->config->cookie->path : '/';
      $domain = $this->config->cookie->domain ? $this->config->cookie->domain : $this->config->domain;
      Cookie::setParams($lifetime, $path, $domain);

      $cookie = Cookie::getInstance();

      return $cookie;
   }

   public function registerHookEventListener()
   {

   }

   /**
    *
    * @param Request $request
    * @return \lzx\core\Controller
    * @throws \Exception
    */
   public function getController(Request $request)
   {
      $ctrler = $request->args[0];
      require_once $this->path['root'] . '/route.php';
      
      if (\array_key_exists($ctrler, $route))
      {
         $ctrlerClass = $route[$ctrler];
         return new $ctrlerClass($request->language, $this->path['language']);
      }
      else
      {
         throw new \Exception('controller not found :(');
      }
   }

}

//__END_OF_FILE__