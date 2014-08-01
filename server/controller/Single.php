<?php

namespace site\controller;

use site\Controller;
use lzx\core\Request;
use lzx\html\Template;
use site\Config;
use lzx\core\Logger;
use lzx\core\Session;
use lzx\core\Cookie;
use site\dbobject\FFComment;
use lzx\db\DB;

/**
 * @property \lzx\db\DB $db database object
 */
abstract class Single extends Controller
{

   protected $db;

   public function __construct( Request $req, Template $html, Config $config, Logger $logger, Session $session, Cookie $cookie )
   {
      parent::__construct( $req, $html, $config, $logger, $session, $cookie );

      Template::$theme = $this->config->theme[ 'single' ];

      if ( $this->session->loginStatus !== TRUE && \file_exists( '/tmp/single' ) )
      {
         $this->_register_end = TRUE;
      }

      $this->db = DB::getInstance();

      // set template $min varible for JS and CSS
      $this->html->var[ 'min' ] = $this->config->stage === Config::STAGE_PRODUCTION ? TRUE : FALSE;
   }

   /**
    * 
    * observer interface
    */
   public function update( Template $html )
   {
      $html->detach( $this );
   }

   protected function _getChart( $activity )
   {
      $data = $this->_getAgeStatJSON( $activity[ 'id' ] );

      $stat = [
         [
            'title' => $activity[ 'name' ] . ' 女生 (' . $data[ 0 ][ 'total' ] . ')人',
            'data' => $data[ 0 ][ 'json' ],
            'div_id' => 'stat_' . $activity[ 'id' ] . '_female'
         ],
         [
            'title' => $activity[ 'name' ] . ' 男生 (' . $data[ 1 ][ 'total' ] . ')人',
            'data' => $data[ 1 ][ 'json' ],
            'div_id' => 'stat_' . $activity[ 'id' ] . '_male'
         ],
      ];

      return new Template( 'chart', ['stat' => $stat ] );
   }

   protected function _getAgeStatJSON( $aid )
   {
      $counts = $this->db->query( 'CALL get_age_stat_single(' . $aid . ')' );
      $ages = [
         '<=22' => 0,
         '23~25' => 0,
         '26~28' => 0,
         '29~31' => 0,
         '32~34' => 0,
         '>=35' => 0
      ];
      $dist = [
         0 => $ages,
         1 => $ages
      ];
      $stat = [
         0 => [ ],
         1 => [ ]
      ];
      $total = [
         0 => 0,
         1 => 0
      ];

      foreach ( $counts as $c )
      {
         $sex = (int) $c[ 'sex' ];

         $total[ $sex ] += (int) $c[ 'count' ];

         if ( $c[ 'age' ] < 23 )
         {
            $dist[ $sex ][ '<=22' ] += $c[ 'count' ];
         }
         elseif ( $c[ 'age' ] < 26 )
         {
            $dist[ $sex ][ '23~25' ] += $c[ 'count' ];
         }
         elseif ( $c[ 'age' ] < 29 )
         {
            $dist[ $sex ][ '26~28' ] += $c[ 'count' ];
         }
         elseif ( $c[ 'age' ] < 32 )
         {
            $dist[ $sex ][ '29~31' ] += $c[ 'count' ];
         }
         elseif ( $c[ 'age' ] < 35 )
         {
            $dist[ $sex ][ '32~34' ] += $c[ 'count' ];
         }
         else
         {
            $dist[ $sex ][ '>=35' ] += $c[ 'count' ];
         }
      }

      foreach ( $dist as $sex => $counts )
      {
         foreach ( $counts as $ages => $count )
         {
            $stat[ $sex ][] = [ $ages, $count ];
         }
      }

      foreach ( $stat as $sex => $counts )
      {
         $stat[ $sex ] = [
            'total' => $total[ $sex ],
            'json' => \json_encode( $counts )
         ];
      }

      return $stat;
   }

   protected function _getComments( $aid, $order = 'DESC' )
   {

      $ffcomments = new FFComment();
      $ffcomments->aid = $aid;
      $ffcomments->order( 'id', $order );
      return new Template( 'comments', ['comments' => $ffcomments->getList() ] );
   }

   protected function _displayLogin()
   {
      $defaultRedirect = '/single/attendee';
      if ( $this->request->referer && $this->request->referer !== '/single/login' )
      {
         $this->session->loginRedirect = $this->request->referer;
      }
      else
      {
         $this->session->loginRedirect = $defaultRedirect;
      }

      $this->html->var[ 'content' ] = new Template( 'login', ['uri' => $this->request->uri ] );
   }

   protected function _getCode( $uid )
   {
      return \crc32( \substr( \md5( 'alexmika' . $uid ), 5, 10 ) );
   }

}

//__END_OF_FILE__