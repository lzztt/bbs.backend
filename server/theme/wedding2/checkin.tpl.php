<?php

foreach ( $tables as $i => $guests ):
   echo '<div class="table"><div class="table_name">桌号: ' . $i . '</div>';
   foreach ( $guests as $g ):
      print '<div id="guest_' . $g[ 'id' ] . '" class="' . ($g[ 'checkin' ] ? 'confirmed' : 'guest') . '">' . $g[ 'name' ] . ' (' . $g[ 'guests' ] . ')</div>';
   endforeach;
   echo '</div>';
endforeach;
?>