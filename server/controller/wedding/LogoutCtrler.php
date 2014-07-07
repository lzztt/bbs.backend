<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace site\controller\wedding;

use site\controller\Wedding;

/**
 * Description of Wedding
 *
 * @author ikki
 */
class LogoutCtrler extends Wedding
{

   public function run()
   {
      $defaultRedirect = '/wedding/listall';

      unset( $this->session->loginStatus );
      if ( $this->request->referer && $this->request->referer !== '/wedding/logout' )
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
