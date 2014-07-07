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
class WeddingCtrler extends Wedding
{

   public function run()
   {
      $this->html->var[ 'body' ] = new Template( 'join_form' );
   }

}
