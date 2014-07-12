<?php

namespace site\controller\single;

use site\controller\Single;

/**
 * @property \lzx\db\DB $db database object
 */
class NodeCtrler extends Single
{
   /**
    * public methods
    */
   public function run()
   {
      $this->request->redirect( '/node/32576' );
   }

}

//__END_OF_FILE__