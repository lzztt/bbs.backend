<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace site\controller\wedding;

use site\controller\Wedding;
use lzx\html\Template;

/**
 * Description of Wedding
 *
 * @author ikki
 */
class LoginCtrler extends Wedding
{

   public function run()
   {
      Template::$theme = $this->config->theme[ 'wedding2' ];

      $defaultRedirect = '/wedding/listall';

      if ( $this->request->post )
      {
         if ( $this->request->post[ 'password' ] == 'alexmika928123' )
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
