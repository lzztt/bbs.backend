<div style="background-color: pink;">
  <?php foreach ($confirmed_groups as $i => $attendees): ?>
    <div id="confirmed_group_<?= $i ?>"><h3>已登记<?= $i ? '男生' : '女生'  ?></h3>
      <?php foreach ($attendees as $i => $a): ?>
        <div class="attendee confirmed"><span><?= $i + 1 ?></span><?= $a['name'] ?></div>
      <?php endforeach ?>
    </div>
  <?php endforeach ?>
  <?php foreach ($unconfirmed_groups as $i => $attendees): ?>
    <div id="unconfirmed_group_<?= $i ?>"><h3>未登记<?= $i ? '男生' : '女生'  ?></h3>
      <?php foreach ($attendees as $i => $a): ?>
        <div id="attendee_<?= $a['id'] ?>" class="attendee unconfirmed"><?= $a['name'] ?></div>
      <?php endforeach ?>
    </div>
  <?php endforeach ?>
</div>