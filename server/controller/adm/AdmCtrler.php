<?php

namespace site\controller\adm;

use site\controller\Adm;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of adm
 *
 * @author ikki
 */
class AdmCtrler extends Adm
{

   public function run()
   {
      $this->redirect = '/adm/ad';
   }

}

//__END_OF_FILE__