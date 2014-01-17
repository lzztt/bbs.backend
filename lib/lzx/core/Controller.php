<?php

namespace lzx\core;

use lzx\core\ControllerAction;

// only controller will handle all exceptions and local languages
// other classes will report status to controller
// controller set status back the WebApp object
// WebApp object will call Theme to display the content

/**
 *
 * @property \lzx\Core\Cache $cache
 * @property \lzx\core\Logger $logger
 * @property \lzx\html\Template $html
 * @property \lzx\core\Request $request
 * @property Array $path
 * @property \lzx\core\Session $session
 * @property \lzx\core\Cookie $cookie
 * @property \lzx\core\Config $config
 *
 */
abstract class Controller
{

   const GUEST_UID = 0;
   const ADMIN_UID = 1;

   public $path;
   public $logger;
   public $cache;
   public $html;
   public $request;
   public $session;
   public $cookie;
   public $config;
   protected static $l = [];
   protected $class;

   public function __construct()
   {
      $this->class = \get_class( $this );
   }

   abstract public function run();

   abstract public function checkAJAX();

   public function runAction( $action )
   {
      $func = $action . 'Action';
      if ( \method_exists( $this, $func ) )
      {
         return $this->$func();
      }
      else
      {
         $actionClass = $this->class . '\\' . $action;
         $action = new $actionClass( $this );
         if ( !$action instanceof ControllerAction )
         {
            throw new \Exception( 'action class ' . $actionClass . 'need to extend ContollerAction class' );
         }
         return $action->run();
      }
   }

   public function loadController( $ctrlerClass )
   {
      if ( \strpos( $ctrlerClass, '\\' ) === FALSE )
      {
         $ctrlerClass = \substr( $this->class, 0, strrpos( $this->class, '\\' ) + 1 ) . $ctrlerClass;
      }

      $ctrler = new $ctrlerClass( $this->request->language, $this->path['language'] );
      $ctrler->path = $this->path;
      $ctrler->logger = $this->logger;
      $ctrler->cache = $this->cache;
      $ctrler->html = $this->html;
      $ctrler->request = $this->request;
      $ctrler->session = $this->session;
      $ctrler->cookie = $this->cookie;

      return $ctrler;
   }

   public function error( $msg, $log = FALSE )
   {
      Cache::$status = FALSE;
      if ( $log )
      {
         $this->logger->error( $msg );
      }
      $this->html->var['content'] = $this->l( 'Error' ) . ' : ' . $msg;
      $this->request->pageExit( (string) $this->html );
   }

   public function l( $key )
   {
      return \array_key_exists( $key, self::$l[$this->class] ) ? self::$l[$this->class][$key] : '[' . $key . ']';
   }

}

//__END_OF_FILE__
