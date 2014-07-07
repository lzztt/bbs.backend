<?php

namespace site\controller;

use site\Controller;
use lzx\html\Template;
use site\dbobject\AD as ADObject;
use site\contoller\adm\AD as AD;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of adm
 *
 * @author ikki
 */
abstract class Adm extends Controller
{

   protected function init()
   {
      $this->cache->setStatus( FALSE );
      
      if ( $this->request->uid !== self::ADMIN_UID )
      {
         $this->request->pageNotFound();
      }
      
      Template::$theme = $this->config->theme['adm'];
   }
   
   public function run()
   {
      $action = $this->args[1] ? $this->args[1] : 'user';
      $this->html->var['content'] = $this->run( $action );
   }
   
   public function ad()
   {
      (new AD())->run();
   }

}

//__END_OF_FILE__