<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace site\controller\single;

use site\controller\Single;

/**
 * Description of Wedding
 *
 * @author ikki
 */
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
      $this->request->redirect( $uri );
   }

}

//__END_OF_FILE__
