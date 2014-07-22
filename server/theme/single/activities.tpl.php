<div id="activities">
   <?php foreach ( $activities as $a ): ?>
      <h3><?php print $a[ 'name' ]; ?> <small><?php print \date( 'Y年 n月 j日', $a[ 'time' ] ); ?> <a href="/node/<?php print $a[ 'nid' ]; ?>">论坛讨论帖</a></small></h3>
      
      <div class="charts"><?php print $a[ 'chart' ]; ?></div>
      <?php print $a[ 'comments' ]; ?>
   <?php endforeach; ?>
</div>
