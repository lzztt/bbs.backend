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
use lzx\core\Cache;
use lzx\core\Session;
use lzx\core\Cookie;
use site\ControllerRouter;
use site\dbobject\User;
use site\dbobject\Tag;
use site\dbobject\SecureLink;

/**
 *
 * @property \lzx\Core\Cache $cache
 * @property \lzx\core\Logger $logger
 * @property \lzx\html\Template $html
 * @property \lzx\core\Request $request
 * @property \lzx\core\Session $session
 * @property \lzx\core\Cookie $cookie
 * @property \site\Config $config
 * @property \site\dbobject\User $user
 *
 */
abstract class Controller extends LzxCtrler
{

   const GUEST_UID = 0;
   const ADMIN_UID = 1;

   public $user = NULL;
   public $args;
   public $id;
   public $config;

   /**
    * public methods
    */
   public function __construct( Request $req, Template $html, Config $config, Logger $logger, Cache $cache, Session $session, Cookie $cookie )
   {
      parent::__construct( $req, $html, $logger, $cache, $session, $cookie );
      $this->config = $config;
      if ( !\array_key_exists( $this->class, self::$l ) )
      {
         $lang_file = $lang_path . \str_replace( '\\', '/', $this->class ) . '.' . Template::$language . '.php';
         if ( \is_file( $lang_file ) )
         {
            include $lang_file;
         }
         self::$l[ $this->class ] = isset( $language ) ? $language : [ ];
      }

      // register this controller as an observer of the HTML template
      $this->html->attach( $this );
   }

   /**
    * SPLObserver interface
    */
   public function update( \SplSubject $html )
   {
      static $updated = FALSE;

      if ( !$updated )
      {
         // update user info
         if ( $this->request->uid > 0 )
         {
            $this->user = new User( $this->request->uid, NULL );
            // update access info
            $this->user->call( 'update_access_info(' . $this->request->uid . ',' . $this->request->timestamp . ',' . \ip2long( $this->request->ip ) . ')' );
            // check new pm message
            $this->cookie->pmCount = \intval( $this->user->getPrivMsgsCount( 'new' ) );
            $updateUserInfo = FALSE;
         }

         // set navbar
         $navbar = $this->cache->fetch( 'page_navbar' );
         if ( $navbar === FALSE )
         {
            $vars = [
               'forumMenu' => $this->_createMenu( Tag::FORUM_ID ),
               'ypMenu' => $this->_createMenu( Tag::YP_ID ),
               'uid' => $this->request->uid
            ];
            $navbar = new Template( 'page_navbar', $vars );
            $this->cache->store( 'page_navbar', $navbar );
         }
         $html->var[ 'page_navbar' ] = $navbar;

         // set headers
         $html->var[ 'head_description' ] = '休斯顿 华人, 黄页, 移民, 周末活动, 旅游, 单身 交友, Houston Chinese, 休斯敦, 休士頓';
         $html->var[ 'head_title' ] = '缤纷休斯顿华人网';

         // mark updated
         $updated = TRUE;
      }
   }

   protected function ajax( $return )
   {
      // set default response data type
      $type = $this->request->get[ 'type' ];
      if ( !\in_array( $type, ['json', 'html', 'text' ] ) )
      {
         $type = 'json';
      }

      if ( $type == 'json' )
      {
         $return = \json_encode( $return );
         if ( $return === FALSE )
         {
            $return = ['error' => $this->l( 'ajax_json_encode_error' ) ];
            $return = \json_encode( $return );
         }
      }
      else
      {
         if ( \is_array( $return ) )
         {
            if ( \sizeof( $return ) == 1 && \array_key_exists( 'error', $return ) )
            {
               $return = $this->l( 'Error' ) . ' : ' . $return[ 'error' ];
            }
         }

         if ( !\is_string( $return ) )
         {
            $return = $this->l( 'Error' ) . ' : ' . $this->l( 'ajax_data_type_error' );
         }
      }

      $this->request->pageExit( $return );
   }

   protected function _forward( $uri )
   {
      $ctrler = ControllerRouter::create( $this->request, $this->html, $this->config, $this->logger, $this->cache, $this->session, $this->cookie, $uri );
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
      $this->request->pageExit( $this->html );
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
      if ( Tag::FORUM_ID == $root_id )
      {
         $type = 'forum';
      }
      else if ( Tag::YP_ID == $root_id )
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

      $pageNo = $this->request->get[ 'page' ] ? (int) $this->request->get[ 'page' ] : 1;
      $pageCount = $nTotal > 0 ? \ceil( $nTotal / $nPerPage ) : 1;
      if ( $pageNo < 1 || $pageNo > $pageCount )
      {
         $pageNo = $pageCount;
      }

      return [$pageNo, $pageCount ];
   }

   protected function _getUserLinks( $activeLink )
   {
      if ( $this->request->uid )
      {
         // user
         return $this->html->linkList( [
               '/user/' . $this->request->uid => '用户首页',
               '/pm/mailbox' => '站内短信',
               '/user/' . $this->request->uid . '/edit' => '编辑个人资料',
               '/password/' . $this->request->uid . '/change' => '更改密码'
               ], $activeLink
         );
      }
      else
      {
         // guest
         return $this->html->linkList( [
               '/user/login' => '登录',
               '/user/register' => '创建新帐号',
               '/password/forget' => '忘记密码',
               '/user/username' => '忘记用户名'
               ], $activeLink
         );
      }
   }

}

//__END_OF_FILE__
