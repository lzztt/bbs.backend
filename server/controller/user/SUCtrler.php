<?php

namespace site\controller\user;

use site\controller\User;

class SUCtrler extends User
{

   public function run()
   {
      $this->_switchUser();
   }

}

//__END_OF_FILE__