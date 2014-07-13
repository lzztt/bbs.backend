<?php

namespace site\controller;

use site\Controller;
use lzx\core\Request;
use lzx\html\Template;
use site\Config;
use lzx\core\Logger;
use lzx\core\Cache;
use lzx\core\Session;
use lzx\core\Cookie;

abstract class Password extends Controller
{

   public function __construct( Request $req, Template $html, Config $config, Logger $logger, Cache $cache, Session $session, Cookie $cookie )
   {
      parent::__construct( $req, $html, $config, $logger, $cache, $session, $cookie );
      // don't cache user page at page level
      $this->cache->setStatus( FALSE );
   }

   protected function _getUserLinks( $uid, $activeLink )
   {
      if ( $this->request->uid )
      {
         // self or admin
         if ( $this->request->uid == $uid || $this->request->uid == self::ADMIN_UID )
         {
            return $this->html->linkList( [
                  '/user/' . $uid => '用户首页',
                  '/user/' . $uid . '/pm' => '站内短信',
                  '/user/' . $uid . '/edit' => '编辑个人资料',
                  '/password/' . $uid . '/change' => '更改密码'
                  ], $activeLink
            );
         }
      }
      else
      {
         return $this->html->linkList( [
               '/user/login' => '登录',
               '/user/register' => '创建新帐号',
               '/password/reset' => '重设密码',
               '/user/username' => '忘记用户名'
               ], $activeLink
         );
      }
   }

}

//__END_OF_FILE__