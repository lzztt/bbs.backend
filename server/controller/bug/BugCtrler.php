<?php

namespace site\controller\bug;

use site\controller\Bug;

class BugCtrler extends Bug
{

   public function run()
   {
      $this->logger->info($_SERVER['HTTP_USER_AGENT'] . PHP_EOL . '[REFFER] ' . $this->request->referer . PHP_EOL . '[ERROR] ' . $this->request->post['error']);
      $this->response->setContent( NULL );
   }

}

//__END_OF_FILE__
