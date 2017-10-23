<select id="edit_list">
  <option value="">---选择姓名---</option>
  <?php foreach ( $attendees as $a ): ?>
    <option value="/wedding/<?php print $a['id']; ?>/edit"><?php print $a['name']; ?></option>
  <?php endforeach; ?>
</select>
<div id="edit"></div>