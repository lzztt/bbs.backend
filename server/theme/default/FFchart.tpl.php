<?php foreach ($stat as $act): ?>
   <div style="clear: both">
      <?php foreach ($act as $dist): ?>
      <div id="<?php print $dist['div_id']; ?>" style="display: inline-block" ></div>
      <script type='text/javascript'>
         <?php print "drawChart('${dist['title']}', $.parseJSON('${dist['data']}'), '${dist['div_id']}')"; ?>
      </script>
      <?php endforeach; ?>
   </div>
<?php endforeach; ?>

