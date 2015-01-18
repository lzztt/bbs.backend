<?php

namespace site\controller\app;

use site\controller\App;

class CacheCtrler extends App
{

   private $_name = 'cache';

   public function run()
   {
      $this->response->setContent( \file_get_contents( $this->_getLatestVersion( $this->_name ) . '/index.html' ) );
   }

}

//__END_OF_FILE__
