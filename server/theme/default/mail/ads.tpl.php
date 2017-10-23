七天内过期广告列表:
<?php foreach ( $ads as $a ): ?>
  <?php print \date( 'm/d/Y', $a['exp_time'] ) . ' : ' . $a['name'] . ' : ' . $a['email'] . ' : ' . ($a['type_id'] == 1 ? '电子黄页' : '页顶广告') . PHP_EOL; ?>
<?php endforeach; ?>