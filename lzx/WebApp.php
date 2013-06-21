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
use lzx\html\Template;

/**
 *
 * @property lzx\core\Config $config
 * @property lzx\core\Logger $logger
 * @property lzx\core\MySQL $db
 * @property lzx\core\Cache $cache
 * @property lzx\core\Request $request
 * @property lzx\core\Session $session
 */
// cookie->uid
// cookie->urole
// cookie->umode
// session->uid
// session->urole

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
         Handler::$displayError = ($this->config->stage !== 'production');
      }
      catch (\Exception $e)
      {
         $msg = '[longzox] WebApp initialization error: ' . $e->getMessage();
         if ($this->logger instanceof Logger)
         {
            $this->logger->error($msg . \PHP_EOL . $e->getTraceAsString(), FALSE);
         }
         else
         {
            \error_log($msg . \PHP_EOL . $e->getTraceAsString());
         }
         exit($msg);
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

      if ($this->config->stage !== 'production') // DEV or TEST stage
      {
         $db->debugMode = TRUE;
      }

      // get cookie
      $cookie = $this->getCookie();

      // start session
      $session = $this->getSession($cookie);

      // set user info for logger
      $userinfo = 'uid=' . $session->uid
            . ' umode=' . $this->getUmode($cookie)
            . ' urole=' . (isset($cookie->urole) ? $cookie->urole : 'guest');
      $this->logger->setUserInfo($userinfo);

      // set request uid based on session uid
      $request->uid = $session->uid;

      // start cache
      $cache = Cache::getInstance($this->config->cache_path);
      $cache->setLogger($this->logger);
      $cache->setStatus($this->config->cache);

      $this->registerHookEventListener();

      // start template
      Template::$theme = $this->config->theme;
      Template::$path = $this->path['theme'] . '/' . $this->config->theme;
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
         echo $userinfo . \PHP_EOL;
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
               if (!\in_array($key, $get_keys))
               {
                  $this->logger->error('unsupport GET key:' . $key);
                  return FALSE;
               }
            }
         }
      }

      //valid request
      return TRUE;
   }

   /**
    * @param Cookie $cookie
    */
   private function getUmode(Cookie $cookie)
   {
      static $umode;

      if (!isset($umode))
      {
         $umode = $cookie->umode;

         if (!\in_array($umode, array(Template::UMODE_PC, Template::UMODE_MOBILE, Template::UMODE_ROBOT)))
         {
            $agent = $_SERVER['HTTP_USER_AGENT'];
            if (\preg_match('/(iPhone|Android|BlackBerry)/i', $agent))
            {
               // if ($http_user_agent ~ '(iPhone|Android|BlackBerry)') {
               $umode = Template::UMODE_MOBILE;
            }
            elseif (\preg_match('/(http|Yahoo|bot)/i', $agent))
            {
               $umode = Template::UMODE_ROBOT;
               //}if ($http_user_agent ~ '(http|Yahoo|bot)') {
            }
            else
            {
               $umode = Template::UMODE_PC;
            }
            $cookie->umode = $umode;
         }
      }

      return $umode;
   }

   /**
    *
    * @return Request
    */
   public function getRequest()
   {
      $req = Request::getInstance();
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
   public function getSession(Cookie $cookie)
   {
      static $session;

      if (!isset($session))
      {
         $umode = $this->getUmode($cookie);

         if ($umode == Template::UMODE_ROBOT)
         {
            Session::$isDummyInstance = TRUE;
         }
         else
         {
            $lifetime = $this->config->cookie->lifetime;
            $path = $this->config->cookie->path ? $this->config->cookie->path : '/';
            $domain = $this->config->cookie->domain ? $this->config->cookie->domain : $this->config->domain;
            \session_set_cookie_params($lifetime, $path, $domain);
            \session_name('LZXSID');
         }
         $session = Session::getInstance();

         if ($cookie->uid != $session->uid)
         {
            $cookie->clear();
            $session->clear();
         }
      }

      return $session;
   }

   public function getCookie()
   {
      static $cookie;

      if (!isset($cookie))
      {
         $lifetime = $this->config->cookie->lifetime;
         $path = $this->config->cookie->path ? $this->config->cookie->path : '/';
         $domain = $this->config->cookie->domain ? $this->config->cookie->domain : $this->config->domain;
         Cookie::setParams($lifetime, $path, $domain);

         $cookie = Cookie::getInstance();

         // check cookie for robot agent
         $umode = $this->getUmode($cookie);
         if ($umode == Template::UMODE_ROBOT && ($cookie->uid != 0 || isset($cookie->urole)))
         {
            $cookie->uid = 0;
            unset($cookie->urole);
         }

         // check role for guest
         if ($cookie->uid == 0 && isset($cookie->urole))
         {
            unset($cookie->urole);
         }
      }

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