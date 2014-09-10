<?php

namespace site;

// only controller will handle all exceptions and local languages
// other classes will report status to controller
// controller set status back the WebApp object
// WebApp object will call Theme to display the content
use lzx\core\Controller as LzxCtrler;
use lzx\core\Request;
use lzx\html\Template;
use site\Config;
use lzx\core\Logger;
use lzx\core\Session;
use lzx\core\Cookie;
use site\ControllerRouter;
use site\dbobject\User;
use site\dbobject\Tag;
use site\dbobject\SecureLink;
use lzx\cache\CacheEvent;
use lzx\cache\CacheHandler;
use site\dbobject\City;
use site\dbobject\Session as SessionObj;

/**
 *
 * @property \lzx\core\Logger $logger
 * @property \lzx\html\Template $html
 * @property \lzx\core\Request $request
 * @property \lzx\core\Session $session
 * @property \lzx\core\Cookie $cookie
 * @property \site\Config $config
 * @property \site\PageCache $cache
 * @property \site\Cache[]  $_independentCacheList
 * @property \site\CacheEvent[] $_cacheEvents
 * @property \site\dbobject\City $_city
 *
 */
abstract class Controller extends LzxCtrler
{

   const UID_GUEST = 0;
   const UID_ADMIN = 1;

   protected static $_city;
   private static $_requestProcessed = FALSE;
   private static $_cacheHandler;
   public $args;
   public $id;
   public $config;
   public $cache;
   public $site;
   protected $_independentCacheList = [ ];
   protected $_cacheEvents = [ ];

   /**
    * public methods
    */
   public function __construct( Request $req, Template $html, Config $config, Logger $logger, Session $session, Cookie $cookie )
   {
      parent::__construct( $req, $html, $logger, $session, $cookie );

      if ( !self::$_requestProcessed )
      {
         // set site info
         $site = preg_replace( ['/\w*\./', '/bbs.*/' ], '', $this->request->domain, 1 );

         Template::setSite( $site );

         self::$_cacheHandler = CacheHandler::getInstance();
         self::$_cacheHandler->setCacheTreeTable( self::$_cacheHandler->getCacheTreeTable() . '_' . $site );
         self::$_cacheHandler->setCacheEventTable( self::$_cacheHandler->getCacheEventTable() . '_' . $site );

         // validate site for session
         self::$_city = new City();
         self::$_city->uriName = $site;
         self::$_city->load();
         if ( self::$_city->exists() )
         {
            $session = new SessionObj( \session_id() );
            // not a robot
            if ( $session->exists() )
            {
               if ( $session->cid == 0 )
               {
                  $session->cid = self::$_city->id;
                  $session->update( 'cid' );
               }
               else
               {
                  // session city mismatch, clear current cookie and session
                  if ( $session->cid != self::$_city->id )
                  {
                     $this->session->clear();
                     $this->cookie->clear();
                  }
               }
            }
         }
         else
         {
            $this->error( 'unsupported website: ' . $this->request->domain );
         }

         // update user info
         if ( $this->request->uid > 0 )
         {
            $user = new User( $this->request->uid, NULL );
            // update access info
            $user->call( 'update_access_info(' . $this->request->uid . ',' . $this->request->timestamp . ',' . \ip2long( $this->request->ip ) . ')' );
            // check new pm message
            $pmCount = $user->getPrivMsgsCount( 'new' );
            if ( $pmCount != $this->cookie->pmCount )
            {
               $this->cookie->pmCount = (int) $pmCount;
            }
         }

         self::$_requestProcessed = TRUE;
      }

      // language info
      $this->config = $config;
      if ( !\array_key_exists( $this->class, self::$l ) )
      {
         $lang_file = $lang_path . \str_replace( '\\', '/', $this->_class ) . '.' . Template::$language . '.php';
         if ( \is_file( $lang_file ) )
         {
            include_once $lang_file;
         }
         self::$l[ $this->_class ] = isset( $language ) ? $language : [ ];
      }

      // register this controller as an observer of the HTML template
      $this->html->attach( $this );
   }

   public function flushCache()
   {
      if ( $this->config->cache )
      {
         if ( $this->cache && Template::getStatus() === TRUE )
         {
            $this->cache->store( $this->html );
            $this->cache->flush();
            $this->cache = NULL;
         }

         foreach ( $this->_independentCacheList as $s )
         {
            $s->flush();
         }

         foreach ( $this->_cacheEvents as $e )
         {
            $e->flush();
         }
      }
   }

   /**
    * Observer design pattern interface
    */
   public function update( Template $html )
   {
      // set navbar
      $navbarCache = $this->_getIndependentCache( 'page_navbar' );
      $navbar = $navbarCache->fetch();
      if ( !$navbar )
      {
         if ( self::$_city->YPRootID )
         {
            $vars = [
               'forumMenu' => $this->_createMenu( self::$_city->ForumRootID ),
               'ypMenu' => $this->_createMenu( self::$_city->YPRootID ),
               'uid' => $this->request->uid
            ];
         }
         else
         {
            $vars = [
               'forumMenu' => $this->_createMenu( self::$_city->ForumRootID ),
               'uid' => $this->request->uid
            ];
         }

         $navbar = new Template( 'page_navbar', $vars );
         $navbarCache->store( $navbar );
      }
      $html->var[ 'page_navbar' ] = $navbar;

      // set headers
      if ( !$html->var[ 'head_title' ] )
      {
         $html->var[ 'head_title' ] = '缤纷' . self::$_city->name . '华人网';
      }

      if ( !$this->html->var[ 'head_description' ] )
      {
         $html->var[ 'head_description' ] = self::$_city->name . ' 华人, 黄页, 移民, 周末活动, 旅游, 单身 交友, UTD, UT-Dallas, University of Texas at Dallas, ' . \ucfirst( self::$_city->uriName ) . ' Chinese, ' . self::$_city->uriName . 'bbs';
      }
      else
      {
         $html->var[ 'head_description' ] = $html->var[ 'head_description' ] . ', ' . self::$_city->name . ' 华人, ' . \ucfirst( self::$_city->uriName ) . ' Chinese, ' . self::$_city->uriName . 'bbs';
      }
      $html->var['sitename'] = '缤纷' . self::$_city->name . '华人网';

      // remove this controller from the subject's observer list
      $html->detach( $this );
   }

   protected function ajax( $return )
   {
      $return = \json_encode( $return );
      if ( $return === FALSE )
      {
         $return = [ 'error' => 'ajax json encode error' ];
         $return = \json_encode( $return );
      }
      $this->html = $return;
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

   protected function _forward( $uri )
   {
      $this->html->detach( $this );
      $newReq = clone $this->request;
      $newReq->uri = $uri;
      $ctrler = ControllerRouter::create( $newReq, $this->html, $this->config, $this->logger, $this->session, $this->cookie );
      $ctrler->request = $this->request;
      $ctrler->run();
   }

   protected function _setLoginRedirect( $uri )
   {
      $this->cookie->loginRedirect = $uri;
   }

   protected function _getLoginRedirect()
   {
      $uri = $this->cookie->loginRedirect;
      unset( $this->cookie->loginRedirect );
      return $uri;
   }

   protected function _displayLogin( $redirect = NULL )
   {
      if ( $redirect )
      {
         $this->_setLoginRedirect( $redirect );
      }
      $this->html->var[ 'content' ] = new Template( 'user_login', ['userLinks' => $this->_getUserLinks( '/user/login' ) ] );
   }

   protected function _createSecureLink( $uid, $uri )
   {
      $slink = new SecureLink();
      $slink->uid = $uid;
      $slink->time = $this->request->timestamp;
      $slink->code = \mt_rand();
      $slink->uri = $uri;
      $slink->add();
      return $slink;
   }

   /**
    * 
    * @param type $uri
    * @return \site\dbobject\SecureLink|null
    */
   protected function _getSecureLink( $uri )
   {
      $arr = \explode( '?', $uri );
      if ( \sizeof( $arr ) == 2 )
      {
         $l = new SecureLink();

         $l->uri = $arr[ 0 ];
         \parse_str( $arr[ 1 ], $get );

         if ( isset( $get[ 'r' ] ) && isset( $get[ 'u' ] ) && isset( $get[ 'c' ] ) && isset( $get[ 't' ] ) )
         {
            $l->id = $get[ 'r' ];
            $l->uid = $get[ 'u' ];
            $l->code = $get[ 'c' ];
            $l->time = $get[ 't' ];
            $l->load( 'id' );
            if ( $l->exists() )
            {
               return $l;
            }
         }
      }

      return NULL;
   }

   /*
    * create menu tree for root tags
    */

   protected function _createMenu( $tid )
   {
      $tag = new Tag( $tid, NULL );
      $tree = $tag->getTagTree();
      $type = 'tag';
      $root_id = \array_shift( \array_keys( $tag->getTagRoot() ) );
      if ( self::$_city->ForumRootID == $root_id )
      {
         $type = 'forum';
      }
      else if ( self::$_city->YPRootID == $root_id )
      {
         $type = 'yp';
      }
      $liMenu = '';

      if ( \sizeof( $tree ) > 0 )
      {
         foreach ( $tree[ $tid ][ 'children' ] as $branch_id )
         {
            $branch = $tree[ $branch_id ];
            $liMenu .= '<li><a title="' . $branch[ 'name' ] . '" href="/' . $type . '/' . $branch[ 'id' ] . '">' . $branch[ 'name' ] . '</a>';
            if ( \sizeof( $branch[ 'children' ] ) )
            {
               $liMenu .= '<ul style="display: none;">';
               foreach ( $branch[ 'children' ] as $leaf_id )
               {
                  $leaf = $tree[ $leaf_id ];
                  $liMenu .= '<li><a title="' . $leaf[ 'name' ] . '" href="/' . $type . '/' . $leaf[ 'id' ] . '">' . $leaf[ 'name' ] . '</a></li>';
               }
               $liMenu .= '</ul>';
            }
            $liMenu .= '</li>';
         }
      }

      return $liMenu;
   }

   protected function _getPagerInfo( $nTotal, $nPerPage )
   {
      if ( $nPerPage <= 0 )
      {
         throw new \Exception( 'invalid value for number of items per page: ' . $nPerPage );
      }

      $pageNo = $this->request->get[ 'p' ] ? (int) $this->request->get[ 'p' ] : 1;
      $pageCount = $nTotal > 0 ? \ceil( $nTotal / $nPerPage ) : 1;
      if ( $pageNo < 1 || $pageNo > $pageCount )
      {
         $pageNo = $pageCount;
      }

      return [ $pageNo, $pageCount ];
   }

   protected function _getUserLinks( $activeLink )
   {
      if ( $this->request->uid )
      {
         // user
         return $this->html->navbar( [
               '用户首页' => '/user/' . $this->request->uid,
               '站内短信' => '/pm/mailbox',
               '收藏夹' => '/user/' . $this->request->uid . '/bookmark',
               '编辑个人资料' => '/user/' . $this->request->uid . '/edit',
               '更改密码' => '/password/' . $this->request->uid . '/change'
               ], $activeLink
         );
      }
      else
      {
         // guest
         return $this->html->navbar( [
               '登录' => '/user/login',
               '创建新帐号' => '/user/register',
               '忘记密码' => '/password/forget',
               '忘记用户名' => '/user/username'
               ], $activeLink
         );
      }
   }

}

//__END_OF_FILE__
