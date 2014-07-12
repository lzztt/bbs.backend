<?php

namespace site\controller\user;

use site\controller\User;

class PasswordCtrler extends User
{

   public function run()
   {
      // forward to password controller
      if ( $this->request->uid == self::GUEST_UID )
      {
         $this->_forward( '/password/forget' );
      }
      else
      {
         $this->_forward( '/password/change' );
      }
   }

}

//__END_OF_FILE__
