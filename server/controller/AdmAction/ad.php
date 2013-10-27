<?php

namespace site\controller\AdmAction;

use lzx\core\Controller;
use lzx\html\Template;
use site\dataobject\AD as ADObject;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of adm
 *
 * @author ikki
 */
class ad
{

   public function run()
   {
      $page = $this->controller->loadController( 'Page' );
      $page->updateInfo();
      $page->setPage();

      $ad = new ADObject();
      $a_month_ago = $this->request->timestamp - 2592000;
      $contents = array(
         'ads' => $ad->getAllAds( $a_month_ago ),
         'payments' => $ad->getAllAdPayment( $a_month_ago )
      );
      return new Template( 'ads', $contents );
   }

}

//__END_OF_FILE__
