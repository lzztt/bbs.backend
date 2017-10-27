<?php foreach ($stat as $act): ?>
  <div style="clear: both">
    <?php foreach ($act as $dist): ?>
    <div id="<?= $dist['div_id'] ?>" style="display: inline-block" ></div>
    <script type='text/javascript'>
      <?= "drawChart('${dist['title']}', $.parseJSON('${dist['data']}'), '${dist['div_id']}')" ?>
    </script>
    <?php endforeach ?>
  </div>
<?php endforeach ?>

