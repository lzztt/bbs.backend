<?php foreach ( $stat as $dist ): ?>
  <div class="google_chart" id="<?php print $dist['div_id']; ?>" data-title='<?php print $dist['title']; ?>' data-json='<?php print $dist['data']; ?>'></div>
<?php endforeach; ?>