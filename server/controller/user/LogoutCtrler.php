<?php

namespace site\controller\user;

use site\controller\User;
use lzx\html\Template;

class LogoutCtrler extends User
{

   public function run()
   {
      if ( $this->request->uid == self::GUEST_UID )
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
      $this->cookie->uid = 0;
      $this->cookie->urole = NULL;
      $this->cookie->pmCount = 0;
      $this->pageRedirect( '/' );
   }

}

//__END_OF_FILE__
