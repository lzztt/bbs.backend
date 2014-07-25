<?php

namespace site\controller\single;

use site\controller\Single;
use site\dbobject\FFAttendee;
use lzx\html\Template;

class CheckinCtrler extends Single
{

   public function run()
   {
      // login first
      if ( !$this->session->loginStatus )
      {
         $this->_displayLogin();
         return;
      }

      // logged in    
      $act = \array_pop( $this->db->query( 'CALL get_latest_single_activity()' ) );
      $atd = new FFAttendee();
      $atd->aid = (int) $act['id'];
      $atd->status = 1;
      
      $attendeeGroups = [
         0 => [ ],
         1 => [ ] ];

      foreach ( $atd->getList('id,name,sex,email,checkin') as $attendee )
      {
         $attendeeGroups[ (int) $attendee[ 'sex' ] ][] = $attendee;
      }

      $this->html->var[ 'content' ] = new Template( 'checkin', [ 'groups' => $attendeeGroups ] );
   }

}

//__END_OF_FILE__
