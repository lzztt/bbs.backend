<?php

namespace site\controller\app;

use site\controller\App;

class TopicCtrler extends App
{

   private $_name = 'topic';

   public function run()
   {
      $this->response->setContent( \file_get_contents( $this->_getLatestVersion( $this->_name ) . '/index.html' ) );
   }

}

//__END_OF_FILE__
