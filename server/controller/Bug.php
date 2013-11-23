<?php

namespace site\controller;

use site\Controller;

class Bug extends Controller
{

   public function run()
   {
      $this->logger->info($_SERVER['HTTP_USER_AGENT'] . PHP_EOL . '[REFFER] ' . $this->request->referer . PHP_EOL . '[ERROR] ' . $this->request->post['error']);
      exit;
   }

}

//__END_OF_FILE__
