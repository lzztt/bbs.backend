<?php

namespace site;

use lzx\core\Request;
use lzx\html\Template;
use site\Config;
use lzx\core\Logger;
use lzx\core\Cache;
use lzx\core\Session;
use lzx\core\Cookie;

/**
 * Description of ControllerFactory
 *
 * @author ikki
 * use latest static binding
 */
class ControllerFactory
{

   protected static $_route = [ ];

   public static function create( Request $req, Template $html, Config $config, Logger $logger, Cache $cache, Session $session, Cookie $cookie, $forwardURI = NULL )
   {
      $args = $req->getURIargs( $forwardURI ? $forwardURI : $req->uri  );
      $count = \sizeof( $args );
      if ( $count == 0 )
      {
         $ctrler = 'home';
      }
      else
      {
         $ctrler = \array_shift( $args );

         if ( $count == 1 || \is_numeric( $args[ 0 ] ) )
         {
            $ctrler = $ctrler;
         }
         else
         {
            $ctrler = $ctrler . '/' . \array_shift( $args );
         }
      }

      $ctrlerClass = static::$_route[ $ctrler ];

      if ( $ctrlerClass )
      {
         $ctrlerObj = new $ctrlerClass( $req, $html, $config, $logger, $cache, $session, $cookie );
         $ctrlerObj->args = $args;
         return $ctrlerObj;
      }

      // cannot find a controller
      $req->pageNotFound( 'controller not found :(' );
   }

}
