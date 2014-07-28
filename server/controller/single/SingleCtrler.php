<?php

namespace site\controller\single;

use site\controller\Single;
use lzx\html\Template;
use site\PageCache;

/**
 * @property \lzx\db\DB $db database object
 */
class SingleCtrler extends Single
{

   // show activity details
   public function run()
   {
      $this->cache = new PageCache( $this->request->uri );
      
      $a = \array_pop( $this->db->query( 'CALL get_latest_single_activity()' ) );

      $this->html->var[ 'title' ] = $a[ 'name' ];
      $this->html->var[ 'content' ] = new Template( 'home', [
         'activity' => new Template( 'join_form', ['activity' => $a ] ),
         'comments' => $this->_getComments( $a[ 'id' ] ),
         'statistics' => $this->_getChart( $a )
         ] );
   }

}

//__END_OF_FILE__