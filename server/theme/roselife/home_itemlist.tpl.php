<ul class="even_odd_parent">
   <?php foreach ( $data as $n ): ?>
      <li data-after='<?php print $n[ 'after' ]; ?>'><a href="<?php print $n[ 'uri' ]; ?>"><?php print $n[ 'text' ]; ?></a></li>
   <?php endforeach; ?>
</ul>