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
 * @property \lzx\core\Session $session
 * @property \lzx\core\Cookie $cookie
 *
 */
abstract class Controller
{
   protected static $l = [];

   public $logger;
   public $cache;
   public $html;
   public $request;
   public $session;
   public $cookie;
   
   protected $class;

   public function __construct()
   {
      $this->class = \get_class( $this );
   }

   abstract public function run( $method );

   protected function error( $msg, $log = FALSE )
   {
      Cache::$status = FALSE;
      if ( $log )
      {
         $this->logger->error( $msg );
      }
      $this->html->var['content'] = $this->l( 'Error' ) . ' : ' . $msg;
      $this->request->pageExit( (string) $this->html );
   }

   protected function l( $key )
   {
      return '[' . $key . ']';
      //return \array_key_exists( $key, self::$l[$this->class] ) ? self::$l[$this->class][$key] : '[' . $key . ']';
   }

}

//__END_OF_FILE__
