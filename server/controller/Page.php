<?php

namespace site\controller;

use lzx\core\Controller;
use lzx\html\Template;
use site\dataobject\Tag;
use site\dataobject\User as UserObject;

/**
 * Description of navbar
 *
 * @author ikki
 */
class Page extends Controller
{

   public function run()
   {
      // do not allowed to be called from web URL
      $this->request->pageNotFound();
   }

   public function updateInfo()
   {
      $this->updateAccessInfo();
      $this->checkNewPMCount();
   }

   public function setPage()
   {
      $this->setCSSJS();
      $this->setNavbar();
      $this->setTitle();
   }

   public function updateAccessInfo()
   {
      $uid = $this->request->uid;
      if ($uid > 0)
      {
         $user = new UserObject($uid, 'lastAccessIPInt');
         $user->lastAccessTime = $this->request->timestamp;
         $ip = (int) \ip2long($this->request->ip);
         if ($ip != $user->lastAccessIPInt)
         {
            $user->lastAccessIPInt = $ip;
            $user->update('lastAccessTime,lastAccessIPInt');
            $this->cache->delete('authorPanel' . $uid);
         }
         else
         {
            $user->update('lastAccessTime');
         }
      }
   }

   public function checkNewPMCount()
   {
      if ($this->request->uid > 0)
      {
         $user = new UserObject();
         $user->uid = $this->request->uid;
         $pmCount = \intval($user->getNewPrivMsgsCount());
         $this->cookie->pmCount = $pmCount;
      }
   }

   public function setNavbar()
   {
      $navbar = $this->cache->fetch('page_navbar');
      if ($navbar === FALSE)
      {
         $vars = array(
            'forumMenu' => Tag::createMenu('forum'),
            'ypMenu' => Tag::createMenu('yp'),
            'uid' => $this->request->uid
         );
         $navbar = new Template('page_navbar', $vars);
         $this->cache->store('page_navbar', $navbar);
      }
      $this->html->var['page_navbar'] = $navbar;
   }

   public function setTitle()
   {
      $this->html->var['head_description'] = '休斯顿 华人, 黄页, 移民, 周末活动, 旅游, 单身 交友, Houston Chinese, 休斯敦, 休士頓';
      $this->html->var['head_title'] = '缤纷休斯顿华人网';
   }

   public function setCSSJS()
   {
      /*
        $js = array(/*
        '/themes/' . Template::$theme . '/js/cookie.js',
        //'/themes/' . Template::$theme . '/js/jquery-1.8.3.js',
        //'/themes/' . Template::$theme . '/js/jquery-1.9.1.js',
        '/themes/' . Template::$theme . '/js/jquery.upload-1.0.2.js',
        //'/markitup/jquery.markitup.js',
        //'/markitup/sets/bbcode/set.js',
        '/themes/' . Template::$theme . '/js/jquery.markitup.js',
        '/themes/' . Template::$theme . '/js/jquery.markitup.bbcode.set.js',
        '/themes/' . Template::$theme . '/js/hoverIntent.js',
        '/themes/' . Template::$theme . '/js/superfish.js',
        '/themes/' . Template::$theme . '/js/coin-slider.js',
        '/themes/' . Template::$theme . '/js/jquery.MetaData.js',
        '/themes/' . Template::$theme . '/js/jquery.rating.js',
        '/themes/' . Template::$theme . '/js/main.js',
        //'/themes/' . Template::$theme . '/js/minpc_1354442581.js',
       * 
       */
      /*
        );
        $css = array(
        /*
        '/themes/' . Template::$theme . '/css/default.css',
        '/themes/' . Template::$theme . '/css/system.css',
        '/themes/' . Template::$theme . '/css/houstonbbs.css',
        '/themes/' . Template::$theme . '/css/html-elements.css',
        '/themes/' . Template::$theme . '/css/advanced_forum.css',
        '/themes/' . Template::$theme . '/css/advanced_forum-structure.css',
        '/themes/' . Template::$theme . '/css/superfish.css',
        //'/markitup/skins/markitup/style.css',
        //'/markitup/sets/bbcode/style.css',
        '/themes/' . Template::$theme . '/css/markitup.style.css',
        '/themes/' . Template::$theme . '/css/markitup.bbcode.css',
        '/themes/' . Template::$theme . '/css/coin-slider-styles.css',
        '/themes/' . Template::$theme . '/css/yp.css',
        '/themes/' . Template::$theme . '/css/jquery.rating.css',
        '/themes/' . Template::$theme . '/css/privatemsg-view.css',
        //'/themes/' . Template::$theme . '/css/minpc_1354442581.css',
       * 
       */
      /*
        );


        $head_css = '';
        foreach ($css as $i)
        {
        $head_css .= '<link rel="stylesheet" media="all" href="' . $i . '" />';
        }
        $this->html->var['head_css'] = $head_css;

        $head_js = '';
        foreach ($js as $i)
        {
        $head_js .= '<script src="' . $i . '"></script>';
        }
        $this->html->var['head_js'] = $head_js;
       *
       */
   }

}

?>
