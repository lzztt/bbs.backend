<?php

namespace site\controller\single;

use site\controller\Single;

/**
 * @property \lzx\db\DB $db database object
 */
class CommentCtrler extends Single
{

   // public comments
   public function run()
   {
      echo $this->request->post ? $this->_addComment() : $this->_viewComment();
      exit;
   }

}

//__END_OF_FILE__