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
class LoginCtrler extends Single
{

   public function run()
   {
      $defaultRedirect = '/single/attendee';

      if ( $this->request->post )
      {
         if ( $this->request->post[ 'password' ] == 'alexmika6630' )
         {
            $this->session->loginStatus = TRUE;
            $uri = $this->session->loginRedirect;
            unset( $this->session->loginRedirect );
            $this->request->redirect( $uri ? $uri : $defaultRedirect  );
         }
      }

      $this->_displayLogin();
   }

}

//__END_OF_FILE__
