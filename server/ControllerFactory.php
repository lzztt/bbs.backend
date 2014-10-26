<?php

namespace site;

use lzx\core\Request;
use lzx\core\Response;
use site\Config;
use lzx\core\Logger;
use lzx\core\Session;

/**
 * Description of ControllerFactory
 *
 * @author ikki
 * use latest static binding
 */
class ControllerFactory
{

   protected static $_route = [ ];

   /**
    * 
    * @param \lzx\core\Request $req
    * @param \lzx\html\Template $html
    * @param \site\Config $config
    * @param \lzx\core\Logger $logger
    * @param \lzx\core\Session $session
    * @return \site\Controller
    */
   public static function create( Request $req, Response $response, Config $config, Logger $logger, Session $session )
   {
      $id = NULL;
      $args = $req->getURIargs( $req->uri );
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
         $ctrlerObj = new $ctrlerClass( $req, $response, $config, $logger, $session );
         $ctrlerObj->args = $args;
         $ctrlerObj->id = $id;
         return $ctrlerObj;
      }

      // cannot find a controller
      $response->pageNotFound();
      throw new \Exception();
   }

}

//__END_OF_FILE__
