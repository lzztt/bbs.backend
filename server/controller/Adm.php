<?php

namespace site\controller;

use site\Controller;
use lzx\html\Template;
use site\dbobject\AD as ADObject;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of adm
 *
 * @author ikki
 */
class Adm extends Controller
{

   protected function _default()
   {
      Template::$theme = $this->config->theme['adm'];

      

      $this->cache->setStatus( FALSE );
      if ( $this->request->uid !== self::ADMIN_UID )
      {
         $this->request->pageNotFound();
      }

      $action = $this->args[1] ? $this->args[1] : 'user';
      $this->html->var['content'] = $this->run( $action );
   }

}

//__END_OF_FILE__