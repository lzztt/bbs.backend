<?php

namespace site\controller;

use site\Controller;

class Bug extends Controller
{

   protected function _default()
   {
      $this->logger->info($_SERVER['HTTP_USER_AGENT'] . PHP_EOL . '[REFFER] ' . $this->request->referer . PHP_EOL . '[ERROR] ' . $this->request->post['error']);
      exit;
   }

}

//__END_OF_FILE__
