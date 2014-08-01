<div style="background-color: pink;">
   <?php foreach ( $confirmed_groups as $i => $attendees ): ?>
      <div id="confirmed_group_<?php print $i; ?>"><h3>已登记<?php print $i ? '男生' : '女生'  ?></h3>
         <?php foreach ( $attendees as $i => $a ): ?>
            <div class="attendee confirmed"><span><?php print $i + 1; ?></span><?php print $a[ 'name' ]; ?></div>
         <?php endforeach; ?>
      </div>
   <?php endforeach; ?>
   <?php foreach ( $unconfirmed_groups as $i => $attendees ): ?>
      <div id="unconfirmed_group_<?php print $i; ?>"><h3>未登记<?php print $i ? '男生' : '女生'  ?></h3>
         <?php foreach ( $attendees as $i => $a ): ?>
            <div id="attendee_<?php print $a[ 'id' ]; ?>" class="attendee unconfirmed"><?php print $a[ 'name' ]; ?></div>
         <?php endforeach; ?>
      </div>
   <?php endforeach; ?>
</div>