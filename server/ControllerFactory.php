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
      $id = NULL;
      $args = $req->getURIargs( $forwardURI ? $forwardURI : $req->uri  );
      $count = \sizeof( $args );
      if ( $count == 0 )
      {
         $ctrler = 'home';
      }
      else
      {
         // put first argument into controller
         $ctrler = \array_shift( $args );

         // further check the second (and third) argument
         if ( $count > 1 )
         {
            $v = \array_shift( $args );

            if ( \is_numeric( $v ) )
            {
               // second argument is integer, save as ID
               $id = (int) $v;
               if ( $count > 2 )
               {
                  // add third argument into controller
                  $ctrler = $ctrler . '/' . \array_shift( $args );
               }
            }
            else
            {
               // add second argument into controller
               $ctrler = $ctrler . '/' . $v;
            }
         }
      }

      $ctrlerClass = static::$_route[ $ctrler ];
      if ( $ctrlerClass )
      {
         $ctrlerObj = new $ctrlerClass( $req, $html, $config, $logger, $cache, $session, $cookie );
         $ctrlerObj->args = $args;
         $ctrlerObj->id = $id;
         return $ctrlerObj;
      }

      // cannot find a controller
      $req->pageNotFound( 'controller not found :(' );
   }

}
