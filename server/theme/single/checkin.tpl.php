<div style="background-color: pink;">
   <?php foreach ( $groups as $i => $attendees ): ?>
      <div class="group"><h3><?php print $i ? '男生' : '女生'  ?></h3>
         <?php foreach ( $attendees as $i => $a ): ?>
            <div id="attendee_<?php print $a[ 'id' ]; ?>" class="attendee<?php print $a[ 'checkin' ] ? ' confirmed' : ''; ?>">
               <span><?php print $i + 1; ?></span><?php print $a[ 'name' ]; ?>
            </div>
         <?php endforeach; ?>
      </div>
   <?php endforeach; ?>
</div>