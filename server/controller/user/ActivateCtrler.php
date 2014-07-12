<?php

namespace site\controller\user;

use site\controller\User;

class ActivateCtrler extends User
{

   public function run()
   {
      // forward to password controller
      $this->_forward( '/password/reset' );
   }

}

//__END_OF_FILE__
