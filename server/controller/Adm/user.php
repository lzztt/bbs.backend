<?php

namespace site\controller\Adm;

use lzx\core\ControllerAction;
use lzx\html\Template;
use site\dbobject\AD as ADObject;
use site\dbobject\ADPayment;
use lzx\html\Form;
use lzx\html\Input;
use lzx\html\Select;
use lzx\html\TextArea;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of adm
 *
 * @author ikki
 */
class user extends ControllerAction
{

   public function run()
   {
      $function = $this->request->args[2] ? $this->request->args[2] : 'show';

      if ( \method_exists( $this, $function ) )
      {
         return $this->$function();
      }
      else
      {
         throw new \Exception( $this->l( 'action_not_found' ) . ' : ' . $action );
      }
   }

   public function show()
   {
      return 'admin user page';
   }

}

//__END_OF_FILE__
