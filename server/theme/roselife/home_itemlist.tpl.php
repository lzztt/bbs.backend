<ul class="even_odd_parent">
   <?php foreach ( $data as $n ): ?>
      <li <?php if ( \array_key_exists( 'class', $n ) ): print 'class="' . $n[ 'class' ] . '"'; endif; ?> data-after='<?php print $n[ 'after' ]; ?>'><a href="<?php print $n[ 'uri' ]; ?>"><?php print $n[ 'text' ]; ?></a></li>
<?php endforeach; ?>
</ul>