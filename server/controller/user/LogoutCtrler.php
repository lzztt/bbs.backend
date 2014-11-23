<?php

namespace site\controller\user;

use site\controller\User;
use lzx\html\Template;

class LogoutCtrler extends User
{

   public function run()
   {

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
      $this->response->setContent( '<script>localStorage.removeItem("sessionID"); location.pathname !== "' . $this->request->uri . '"? location.reload() : location.assign("/");</script>' );
   }

}

//__END_OF_FILE__
