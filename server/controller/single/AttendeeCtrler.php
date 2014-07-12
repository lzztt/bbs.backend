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
      if ( TRUE )//$this->request->timestamp < strtotime( "09/16/2013 22:00:00 CDT" ) )
      {
         $db = $this->db;
         $content = [
            'attendees' => $db->query( 'CALL get_attendees_single(' . $this->thirty_two_start . ')' )
         ];

         $this->html->var[ 'content' ] = new Template( 'FFattendee', $content );
      }
      else
      {
         $this->html->var[ 'content' ] = "<p>ERROR: The page you request is not available anymore</p>";
      }
   }

}

//__END_OF_FILE__