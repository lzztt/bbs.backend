<?php

namespace site\controller\node;

use site\controller\Node;
use site\dbobject\Node as NodeObject;
use site\dbobject\User;

class BookmarkCtrler extends Node
{

   public function run()
   {

      if ( $this->request->uid == self::UID_GUEST || !$this->id )
      {
         $this->pageForbidden();
      }

      $nid = $this->id;

      $u = new User( $this->request->uid, NULL );

      $u->addBookmark( $nid );
      $this->html = NULL;
   }

}

//__END_OF_FILE__
