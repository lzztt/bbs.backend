<?php

namespace site;

use lzx\core\Service as LzxService;
use lzx\core\Request;
use lzx\core\Response;
use lzx\core\Logger;
use lzx\core\Mailer;
use lzx\html\Template;
use site\Session;
use lzx\cache\CacheHandler;
use site\Config;
use site\dbobject\City;

// handle RESTful web API
// resource uri: /api/<resource>&action=[get,post,put,delete]

/**
 * @property \site\Session $session
 * @property \site\dbobject\City $_city
 */
abstract class Service extends LzxService
{

   protected static $_city;
   private static $_actions = [ 'get', 'post', 'put', 'delete' ];
   private static $_staticProcessed = FALSE;
   private static $_cacheHandler;
   public $action;
   public $args;
   public $session;
   private $_independentCacheList = [ ];
   private $_cacheEvents = [ ];

   public function __construct( Request $req, Response $response, Logger $logger, Session $session )
   {
      parent::__construct( $req, $response, $logger );
      $this->session = $session;

      if ( !self::$_staticProcessed )
      {
         // set site info
         $site = \preg_replace( ['/\w*\./', '/bbs.*/' ], '', $this->request->domain, 1 );

         self::$_cacheHandler = CacheHandler::getInstance();
         self::$_cacheHandler->setCacheTreeTable( self::$_cacheHandler->getCacheTreeTable() . '_' . $site );
         self::$_cacheHandler->setCacheEventTable( self::$_cacheHandler->getCacheEventTable() . '_' . $site );

         // validate site for session
         self::$_city = new City();
         self::$_city->uriName = $site;
         self::$_city->load();
         if ( self::$_city->exists() )
         {
            if ( self::$_city->id != $this->session->getCityID() )
            {
               $this->session->setCityID( self::$_city->id );
            }
         }
         else
         {
            $this->error( 'unsupported website: ' . $this->request->domain );
         }
      }

      // set action
      if ( \array_key_exists( 'action', $req->get ) && \in_array( $req->get[ 'action' ], self::$_actions ) )
      {
         $this->action = $req->get[ 'action' ];
      }
      else
      {
         $this->action = $req->post ? 'post' : 'get';
      }
   }

   // RESTful get
   public function get()
   {
      $this->forbidden();
   }

   // RESTful post
   public function post()
   {
      $this->forbidden();
   }

   // RESTful put
   public function put()
   {
      $this->forbidden();
   }

   // RESTful delete
   public function delete()
   {
      $this->forbidden();
   }

   public function flushCache()
   {
      $config = Config::getInstance();
      if ( $config->cache )
      {
         foreach ( $this->_independentCacheList as $c )
         {
            $c->flush();
         }

         foreach ( $this->_cacheEvents as $e )
         {
            $e->flush();
         }
      }
   }

   /**
    * 
    * @return \lzx\cache\Cache
    */
   protected function _getIndependentCache( $key )
   {
      $_key = self::$_cacheHandler->getCleanName( $key );
      if ( \array_key_exists( $_key, $this->_independentCacheList ) )
      {
         return $this->_independentCacheList[ $_key ];
      }
      else
      {
         $cache = self::$_cacheHandler->createCache( $_key );
         $this->_independentCacheList[ $_key ] = $cache;
         return $cache;
      }
   }

   /**
    * 
    * @return \lzx\cache\CacheEvent
    */
   protected function _getCacheEvent( $name, $objectID = 0 )
   {
      $_name = self::$_cacheHandler->getCleanName( $name );
      $_objID = (int) $objectID;
      if ( $_objID < 0 )
      {
         $_objID = 0;
      }

      $key = $_name . $_objID;
      if ( \array_key_exists( $key, $this->_cacheEvents ) )
      {
         return $this->_cacheEvents[ $key ];
      }
      else
      {
         $event = new CacheEvent( $_name, $_objID );
         $this->_cacheEvents[ $key ] = $event;
         return $event;
      }
   }

   protected function _getPagerInfo( $nTotal, $nPerPage )
   {
      if ( $nPerPage <= 0 )
      {
         throw new \Exception( 'invalid value for number of items per page: ' . $nPerPage );
      }

      $pageCount = $nTotal > 0 ? \ceil( $nTotal / $nPerPage ) : 1;
      if ( $this->request->get[ 'p' ] )
      {
         if ( $this->request->get[ 'p' ] === 'l' )
         {
            $pageNo = $pageCount;
         }
         elseif ( \is_numeric( $this->request->get[ 'p' ] ) )
         {
            $pageNo = (int) $this->request->get[ 'p' ];

            if ( $pageNo < 1 || $pageNo > $pageCount )
            {
               $this->pageNotFound();
            }
         }
         else
         {
            $this->pageNotFound();
         }
      }
      else
      {
         $pageNo = 1;
      }

      return [ $pageNo, $pageCount ];
   }

   protected function createIdentCode( $uid )
   {
      // generate identCode
      $identCode = [
         'code'    => \mt_rand( 100000, 999999 ),
         'uid'     => $uid,
         'attamps' => 0,
         'expTime' => $this->request->timestamp + 600
      ];

      // save in session
      $this->session->identCode = $identCode;

      return $identCode[ 'code' ];
   }

   protected function parseIdentCode( $code )
   {
      if ( !$this->session->identCode )
      {
         return NULL;
      }

      $idCode = $this->session->identCode;
      if ( $idCode[ 'attamps' ] > 5 || $idCode[ 'expTime' ] < $this->request->timestamp )
      {
         // too many attamps, clear code
         $this->session->identCode = NULL;
         return NULL;
      }

      if ( $code == $idCode[ 'code' ] )
      {
         // valid code, clear code
         $this->session->identCode = NULL;
         return $idCode[ 'uid' ];
      }
      else
      {
         // attamps + 1
         $this->session->identCode[ 'attamps' ] = $identCode[ 'attamps' ] + 1;
         return NULL;
      }
   }

   protected function sendIdentCode( $user )
   {
      // create user action and send out email
      $mailer = new Mailer( 'system' );
      $mailer->to = $user->email;
      $siteName = \ucfirst( self::$_city->uriName ) . 'BBS';
      $mailer->subject = $user->username . '在' . $siteName . '的用户安全验证码';
      $contents = [
         'username'   => $user->username,
         'ident_code' => $this->createIdentCode( $user->id ),
         'sitename'   => $siteName
      ];
      $mailer->body = new Template( 'mail/ident_code', $contents );
      
      return $mailer->send();
   }

}

//__END_OF_FILE__
