<?php

foreach ( $attendees as $i => $a ):
   print '<div id="guest_' . $a['id'] . '" class="' . ($a['checkin'] ? 'confirmed' : 'guest') . '">' . $a['name'] . ' (' . $a['guests'] . ')</div>';
endforeach;
?>