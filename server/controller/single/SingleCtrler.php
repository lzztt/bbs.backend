<?php

namespace site\controller\single;

use site\controller\Single;
use lzx\html\Template;

/**
 * @property \lzx\db\DB $db database object
 */
class SingleCtrler extends Single
{

   // show activity details
   public function run()
   {
      $vids = [ "fhFadSF2vrM", "eBXpRXt-5a8" ];
      $vid = \mt_rand( 0, 100 ) % sizeof( $vids );
      $content = [
         'imageSlider' => $this->_getImageSlider(),
         'vid' => $vids[ $vid ],
      ];
      $this->html->var[ 'content' ] = new Template( 'FFhome', $content );

      $this->db->query( 'CALL update_view_count_single("' . \session_id() . '")' );
   }

}

//__END_OF_FILE__