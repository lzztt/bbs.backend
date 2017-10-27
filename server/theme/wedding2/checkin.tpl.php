<?php foreach ($tables as $i => $guests): ?>
  <div class="table"><div class="table_name">桌号: <?= $i ?></div>
  <?php foreach ($guests as $g): ?>
    <div id="guest_<?= $g['id'] ?>" class="<?= $g['checkin'] ? 'confirmed' : 'guest' ?>"><?= $g['name'] ?> (<?= $g['guests'] ?>)</div>
  <?php endforeach ?>
  </div>
<?php endforeach ?>
