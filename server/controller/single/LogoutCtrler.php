<?php

namespace site\controller\single;

use site\controller\Single;

class LogoutCtrler extends Single
{

   public function run()
   {
      $defaultRedirect = '/single/attendee';

      unset( $this->session->loginStatus );
      if ( $this->request->referer && $this->request->referer !== '/single/logout' )
      {
         $uri = $this->request->referer;
      }
      else
      {
         $uri = $defaultRedirect;
      }
      $this->redirect = $uri;
   }

}

//__END_OF_FILE__
