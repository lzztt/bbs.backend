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

   public function run()
   {
      Template::$theme = $this->config->theme_adm;

      parent::run();

      $this->cache->setStatus( FALSE );
      if ( $this->request->uid !== self::ADMIN_UID )
      {
         $this->request->pageNotFound();
      }

      $action = $this->request->args[1] ? $this->request->args[1] : 'user';
      $this->html->var['content'] = $this->runAction( $action );
   }

}

//__END_OF_FILE__