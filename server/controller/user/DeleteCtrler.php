<?php

namespace site\controller\user;

use site\controller\User;
use site\dbobject\User as UserObject;

class DeleteCtrler extends User
{

   public function run()
   {
      $uid = $this->id;
      if ( $this->request->uid == self::UID_ADMIN && $uid > 1 )  // only admin can delete user, can not delete admin
      {
         $user = new UserObject();
         $user->id = $uid;
         $user->delete();
         foreach ( $user->getAllNodeIDs() as $nid )
         {
            $this->_getIndependentCache( '/node/' . $nid )->delete();
         }
         $this->html->var[ 'content' ] = '用户ID: ' . $uid . '已经从系统中删除。';
      }
      else
      {
         $this->pageForbidden();
      }
   }

}

//__END_OF_FILE__
