<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace site\controller;

use site\Controller;
use lzx\core\Request;
use lzx\html\Template;
use site\Config;
use lzx\core\Logger;
use lzx\core\Cache;
use lzx\core\Session;
use lzx\core\Cookie;

/**
 * Description of Wedding
 *
 * @author ikki
 */
abstract class Wedding extends Controller
{

   private $_register_end = FALSE;

   public function __construct( Request $req, Template $html, Config $config, Logger $logger, Cache $cache, Session $session, Cookie $cookie )
   {
      parent::__construct( $req, $html, $config, $logger, $cache, $session, $cookie );
      // don't cache user page at page level
      $this->cache->setStatus( FALSE );

      Template::$theme = $this->config->theme[ 'wedding' ];

      if ( $this->session->loginStatus !== TRUE && \file_exists( '/tmp/wedding' ) )
      {
         $this->_register_end = TRUE;
      }
   }

   protected function error( $msg )
   {
      $this->html->var[ 'body' ] = '<span style="color:blue;">错误 :</span> ' . $msg;
      $this->request->pageExit( (string) $this->html );
   }

   public function _getTableGuests( Array $guests, $countField )
   {
      $table_guests = [ ];
      $table_counts = [ ];
      $total = 0;
      foreach ( $guests as $g )
      {
         if ( !\array_key_exists( $g[ 'tid' ], $table_guests ) )
         {
            $table_guests[ $g[ 'tid' ] ] = [ ];
            $table_counts[ $g[ 'tid' ] ] = 0;
         }
         $table_guests[ $g[ 'tid' ] ][] = $g;
         $table_counts[ $g[ 'tid' ] ] += $g[ $countField ];
         $total += $g[ $countField ];
      }

      \ksort( $table_guests );

      return [$table_guests, $table_counts, $total ];
   }

   protected function _displayLogin()
   {
      Template::$theme = $this->config->theme[ 'wedding2' ];

      $defaultRedirect = '/wedding/listall';
      if ( $this->request->referer && $this->request->referer !== '/wedding/login' )
      {
         $this->session->loginRedirect = $this->request->referer;
      }
      else
      {
         $this->session->loginRedirect = $defaultRedirect;
      }

      $this->html->var[ 'body' ] = new Template( 'login', ['uri' => $this->request->uri ] );
   }

}
