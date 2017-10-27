<?php foreach ($stat as $dist): ?>
  <div class="google_chart" id="<?= $dist['div_id'] ?>" data-title='<?= $dist['title'] ?>' data-json='<?= $dist['data'] ?>'></div>
<?php endforeach ?>