<?php

namespace site\controller\comment;

use site\controller\Comment;

class CommentCtrler extends Comment
{

   public function run()
   {
      $this->request->pageNotFound();
   }

}

//__END_OF_FILE__