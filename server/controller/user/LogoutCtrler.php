<?php

namespace site\controller\user;

use site\controller\User;
use lzx\html\Template;

class LogoutCtrler extends User
{

   public function run()
   {
      var_dump($this->response->cookie);
      if ( $this->request->uid == self::UID_GUEST )
      {
         $this->error( '错误：您尚未成功登录，不能登出。' );
      }

      // logout to switch back to super user
      if ( isset( $this->session->suid ) )
      {
         $this->_switchUser();
         return;
      }

      //session_destroy();
      $this->session->clear(); // keep session record but clear the whole $_SESSION variable
      $this->response->cookie->uid = 0;
      $this->response->cookie->urole = NULL;
      $this->response->cookie->pmCount = 0;
      $this->pageRedirect( '/' );
   }

}

//__END_OF_FILE__
