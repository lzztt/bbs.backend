<?php

namespace site\api;

use site\Service;

class BugAPI extends Service
{

   public function post()
   {
      $this->logger->warn( $this->request->post );
      $this->response->setContent( NULL );
      $this->_json( NULL );
   }

}

//__END_OF_FILE__
