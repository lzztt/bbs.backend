<?php

namespace site\controller\single;

use site\controller\Single;
use lzx\html\Template;

/**
 * @property \lzx\db\DB $db database object
 */
class AttendeeCtrler extends Single
{

   // private attendee info
   public function run()
   {
      // login first
      if ( !$this->session->loginStatus )
      {
         $this->_displayLogin();
         return;
      }

      // logged in    
      if ( TRUE )//$this->request->timestamp < strtotime( "09/16/2013 22:00:00 CDT" ) )
      {
         $a = \array_pop( $this->db->query( 'CALL get_latest_single_activity()' ) );

         $content = [
            'attendees' => $this->db->query( 'CALL get_attendees_single(' . $a[ 'id' ] . ')' )
         ];

         $this->html->var[ 'content' ] = new Template( 'attendees', $content );
      }
      else
      {
         $this->html->var[ 'content' ] = "<p>ERROR: The page you request is not available anymore</p>";
      }
   }

}

//__END_OF_FILE__