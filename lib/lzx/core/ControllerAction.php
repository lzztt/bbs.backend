<?php

namespace lzx\core;

use lzx\core\Controller;

// only controller will handle all exceptions and local languages
// other classes will report status to controller
// controller set status back the WebApp object
// WebApp object will call Theme to display the content

/**
 *
 * @property \lzx\Core\Controller $controller
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
abstract class ControllerAction
{

   public function __construct( Controller $ctrler )
   {
      $this->ctrler = $ctrler;
      $this->path = $ctrler->path;
      $this->logger = $ctrler->logger;
      $this->cache = $ctrler->cache;
      $this->html = $ctrler->html;
      $this->request = $ctrler->request;
      $this->session = $ctrler->session;
      $this->cookie = $ctrler->cookie;
   }

   abstract public function run();

   public function l( $key )
   {
      $this->controller->l( $key );
   }

}

?>
