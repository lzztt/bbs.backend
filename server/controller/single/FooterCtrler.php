<?php

namespace site\controller\single;

use site\controller\Single;

/**
 * @property \lzx\db\DB $db database object
 */
class FooterCtrler extends Single
{

   public function run()
   {
      echo $this->_footer();
      exit;
   }

}

//__END_OF_FILE__