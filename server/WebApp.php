<?php

namespace site;

use lzx\App;
use lzx\core\Handler;
use lzx\db\DB;
use lzx\core\Request;
use lzx\core\Response;
use lzx\core\Session;
use lzx\core\Cookie;
use lzx\html\Template;
use site\Config;
use site\SessionHandler;
use site\ControllerRouter;
use lzx\cache\Cache;
use lzx\cache\CacheEvent;
use lzx\cache\CacheHandler;

/**
 * @property site\Config $config
 */

require_once \dirname( __DIR__ ) . '/lib/lzx/App.php';

class WebApp extends App
{

   const UMODE_BROWSER = 'browser';
   const UMODE_ROBOT = 'robot';

   protected $config;

   public function __construct()
   {
      parent::__construct();
      // register current namespaces
      $this->loader->registerNamespace( __NAMESPACE__, __DIR__ );

      // load configuration
      $this->config = new Config();
      // display errors on page, turn on debug for DEV stage
      if ( $this->config->stage === Config::STAGE_DEVELOPMENT )
      {
         Handler::$displayError = TRUE;
         DB::$debug = TRUE;
         Template::$debug = TRUE;
      }
      else
      {
         Handler::$displayError = FALSE;
         DB::$debug = FALSE;
         Template::$debug = FALSE;
      }

      $this->logger->setDir( $this->config->path[ 'log' ] );
      $this->logger->setEmail( $this->config->webmaster );

      // config template
      Template::setLogger( $this->logger );
      Template::$path = $this->config->path[ 'theme' ];
      Template::$theme = $this->config->theme[ 'roselife' ];
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
   public function run( $argc = 0, Array $argv = [ ] )
   {
      // website is offline
      if ( $this->config->mode === Config::MODE_OFFLINE )
      {
         $offline_file = $this->config->path[ 'file' ] . '/offline.txt';
         $output = \is_file( $offline_file ) ? \file_get_contents( $offline_file ) : 'Website is currently offline. Please visit later.';
         // return offline page
         echo $output;
         return;
      }

      $request = $this->getRequest();
      $get_count = \count( $request->get );
      if ( $get_count )
      {
         $request->get = \array_intersect_key( $request->get, \array_flip( $this->config->getkeys ) );
         // do not cache page with unsupport get keys
         if ( \count( $request->get ) != $get_count )
         {
            $this->config->cache = FALSE;
         }
      }

      // initialize database connection
      $db = DB::getInstance( $this->config->db );

      // config cache
      CacheHandler::$path = $this->config->path[ 'cache' ];
      $cacheHandler = CacheHandler::getInstance( $db );
      Cache::setHandler( $cacheHandler );
      CacheEvent::setHandler( $cacheHandler );
      Cache::setLogger( $this->logger );

      // initialize cookie and session
      $cookie = $this->getCookie();
      $session = $this->getSession( $cookie );

      // set user info for logger
      $userinfo = [
         'uid' => $session->uid,
         'mode' => $this->_getUmode( $cookie ),
         'role' => $cookie->urole ];
      $this->logger->setUserInfo( $userinfo );

      // update request uid based on session uid
      $request->uid = (int) $session->uid;

      // set response cookie
      $response = Response::getInstance();
      $response->cookie = $cookie;

      // run controller
      $ctrler = NULL;
      try
      {
         $ctrler = ControllerRouter::create( $request, $response, $this->config, $this->logger, $session );
         $ctrler->run();
      }
      catch ( \Exception $e )
      {
         if ( $e->getMessage() )
         {
            $this->logger->error( $e->getMessage(), $e->getTrace() );
            $this->logger->flush();
         }
      }

      // send out response
      $response->send();

      // do extra clean up and heavy stuff here
      if ( $response->getStatus() < 400 )
      {
         try
         {
            // flush session
            $session->close();

            // output debug message?
            $debug = ( $this->config->stage == Config::STAGE_DEVELOPMENT );

            if ( $debug )
            {
               $this->logger->info( $db->queries );
            }

            // flush database
            $db->flush();

            // controller flush cache
            if ( $debug )
            {
               $_timer = \microtime( TRUE );
               $ctrler->flushCache();
               $db->flush();
               $_timer = \microtime( TRUE ) - $_timer;
               $this->logger->info( \sprintf( 'cache flush time: %8.6f', $_timer ) );
            }
            else
            {
               $ctrler->flushCache();
               $db->flush();
            }
         }
         catch ( \Exception $e )
         {
            $this->logger->error( $e->getMessage() );
         }
      }

      // flush logger
      $this->logger->flush();
   }

   /**
    * @param Cookie $cookie
    */
   private function _getUmode( Cookie $cookie )
   {
      static $umode;

      if ( !isset( $umode ) )
      {
         $umode = $cookie->umode;

         if ( !\in_array( $umode, [ self::UMODE_ROBOT, self::UMODE_BROWSER ] ) )
         {
            $agent = $_SERVER[ 'HTTP_USER_AGENT' ];
            if ( \preg_match( '/(http|Yahoo|bot|pider)/i', $agent ) )
            {
               $umode = self::UMODE_ROBOT;
            }
            else
            {
               $umode = self::UMODE_BROWSER;
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
      if ( !isset( $req->language ) )
      {
         $req->language = $this->config->language;
      }
      return $req;
   }

   /**
    *
    * @return Session
    */
   public function getSession( Cookie $cookie )
   {
      static $session;

      if ( !isset( $session ) )
      {
         $umode = $this->_getUmode( $cookie );

         if ( $umode == self::UMODE_ROBOT )
         {
            $handler = NULL;
         }
         else
         {
            $lifetime = $this->config->cookie[ 'lifetime' ];
            $path = $this->config->cookie[ 'path' ] ? $this->config->cookie[ 'path' ] : '/';
            $domain = $this->config->cookie[ 'domain' ] ? $this->config->cookie[ 'domain' ] : $this->config->domain;
            \session_set_cookie_params( $lifetime, $path, $domain );
            \session_name( 'LZXSID' );
            $handler = new SessionHandler( DB::getInstance() );
         }
         $session = Session::getInstance( $handler );

         if ( $cookie->uid != $session->uid )
         {
            $cookie->clear();
            $session->clear();
         }
      }

      return $session;
   }

   /**
    * 
    * @staticvar type $cookie
    * @return Cookie
    */
   public function getCookie()
   {
      static $cookie;

      if ( !isset( $cookie ) )
      {
         $lifetime = $this->config->cookie[ 'lifetime' ];
         $path = $this->config->cookie[ 'path' ] ? $this->config->cookie[ 'path' ] : '/';
         $domain = $this->config->cookie[ 'domain' ] ? $this->config->cookie[ 'domain' ] : $this->config->domain;
         Cookie::setParams( $lifetime, $path, $domain );

         $cookie = Cookie::getInstance();

         // check cookie for robot agent, mark as guest
         $umode = $this->_getUmode( $cookie );
         if ( $umode == self::UMODE_ROBOT && $cookie->uid != 0 )
         {
            $cookie->setNoSend();
            $cookie->uid = 0;
         }

         // check role for guest
         if ( $cookie->uid == 0 && isset( $cookie->urole ) )
         {
            unset( $cookie->urole );
         }
      }

      return $cookie;
   }

}

//__END_OF_FILE__
