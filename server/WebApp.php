<?php

namespace site;

use lzx\App;
use lzx\core\Handler;
use lzx\db\DB;
use lzx\core\Request;
use lzx\core\Session;
use lzx\core\Cookie;
use lzx\html\Template;
use site\Config;
use site\SessionHandler;
use site\ControllerRouter;
use lzx\cache\Cache;
use lzx\cache\CacheEvent;
use lzx\cache\CacheHandler;
use lzx\core\ControllerException;

/**
 *
 * @property lzx\core\Session $session
 * @property site\Config $config
 */
// cookie->uid
// cookie->urole
// session->uid
// session->urole
require_once \dirname( __DIR__ ) . '/lib/lzx/App.php';

class WebApp extends App
{

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
      // set output header
      \header( 'Content-Type: text/html; charset=UTF-8' );

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
      $request->get = \array_intersect_key( $request->get, \array_flip( $this->config->getkeys ) );

      // initialize database connection
      $db = DB::getInstance( $this->config->db );

      // config cache
      CacheHandler::$path = $this->config->path[ 'cache' ];
      $cacheHandler = CacheHandler::getInstance();
      Cache::setHandler( $cacheHandler );
      CacheEvent::setHandler( $cacheHandler );
      Cache::setLogger( $this->logger );

      // initialize cookie and session
      if ( !\array_key_exists( 'nosession', $request->get ) )
      {
         // get cookie
         $cookie = $this->getCookie();

         // start session
         $session = $this->getSession( $cookie );
      }
      else
      {
         // get fake cookie and session, when "nosession" option appears in uri
         $cookie = Cookie::getInstance();
         $cookie->setNoSend();
         $cookie->uid = 0;
         $cookie->urole = Template::UROLE_GUEST;

         $session = Session::getInstance( NULL );
      }

      // set user info for logger
      $userinfo = 'uid=' . $session->uid
         . ' umode=' . $this->getUmode( $cookie )
         . ' urole=' . (isset( $cookie->urole ) ? $cookie->urole : 'guest');
      $this->logger->setUserInfo( $userinfo );

      // update request uid based on session uid
      $request->uid = (int) $session->uid;

      $html = NULL;
      try
      {
         $ctrler = ControllerRouter::create( $request, new Template( 'html' ), $this->config, $this->logger, $session, $cookie );
         $ctrler->run();
         $html = $ctrler->html;
      }
      catch ( ControllerException $e )
      {
         $msg = $e->getMessage();
         switch ( $e->getCode() )
         {
            case ControllerException::PAGE_NOTFOUND:
               \header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 404 Not Found' );
               echo ( $msg ? $msg : '404 Not Found :(' );
               // finish request processing
               \fastcgi_finish_request();

               // flush the log and return
               $this->logger->flush();

               return;
               break;
            case ControllerException::PAGE_FORBIDDEN:
               \header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 403 Forbidden' );
               echo ( $msg ? $msg : '403 Forbidden :(' );
               // finish request processing
               \fastcgi_finish_request();

               // flush the log and return
               $this->logger->flush();

               return;
               break;
            case ControllerException::PAGE_REDIRECT:
               \header( 'Location: ' . $msg );

               // unset output
               $html = NULL;

               // continue
               break;
            default:
               // PAGE_ERROR and others
               echo $msg ? $msg : 'Page Error :(';
               // finish request processing
               \fastcgi_finish_request();

               // flush the log and return
               $this->logger->flush();

               return;
               break;
         }
      }

      // send cookie
      $cookie->send();

      // output page content, if we didn't get an exception
      $outputDebug = ( $this->config->stage == Config::STAGE_DEVELOPMENT && $html instanceof Template );
      if ( $html )
      {
         echo $html;
      }

      // FINISH request processing
      if ( !$outputDebug )
      {
         \fastcgi_finish_request();
      }

      // do extra clean up
      // and heavy stuff here
      $session->close();

      if ( $outputDebug )
      {
         echo '<pre>' . $request->datetime . \PHP_EOL . $this->config->stage . \PHP_EOL;
         echo $userinfo . \PHP_EOL;
         echo \print_r( $db->queries, TRUE );
      }

      $db->flush();

      // do controller cleanup
      $_timer = \microtime( TRUE );
      unset( $ctrler );
      $db->flush();
      $_timer = \microtime( TRUE ) - $_timer;

      if ( $outputDebug )
      {
         echo \sprintf( 'cache flush time: %8.6f', $_timer ) . \PHP_EOL;
         echo '</pre>';
      }

      // flush logger
      $this->logger->flush();
   }

   /**
    * @param Cookie $cookie
    */
   private function getUmode( Cookie $cookie )
   {
      static $umode;

      if ( !isset( $umode ) )
      {
         $umode = $cookie->umode;

         if ( !\in_array( $umode, [Template::UMODE_PC, Template::UMODE_MOBILE, Template::UMODE_ROBOT ] ) )
         {
            $agent = $_SERVER[ 'HTTP_USER_AGENT' ];
            if ( \preg_match( '/(http|Yahoo|bot|pider)/i', $agent ) )
            {
               $umode = Template::UMODE_ROBOT;
               //}if ($http_user_agent ~ '(http|Yahoo|bot)') {
            }
            elseif ( \preg_match( '/(iPhone|Android|BlackBerry)/i', $agent ) )
            {
               // if ($http_user_agent ~ '(iPhone|Android|BlackBerry)') {
               $umode = Template::UMODE_MOBILE;
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
         $umode = $this->getUmode( $cookie );

         if ( $umode == Template::UMODE_ROBOT )
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

         // check cookie for robot agent
         $umode = $this->getUmode( $cookie );
         if ( $umode == Template::UMODE_ROBOT && ($cookie->uid != 0 || isset( $cookie->urole )) )
         {
            $cookie->uid = 0;
            unset( $cookie->urole );
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
