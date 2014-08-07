<?php if ( $images ): ?>
   <ul style="display: none;">
      <?php foreach ( $images as $i ): ?>
         <li data-href="/node/<?php print $i[ 'nid' ]; ?>" data-img="<?php print $i[ 'path' ]; ?>"><?php print $i[ 'name' ] . ' @ ' . $i[ 'title' ]; ?></li>
      <?php endforeach; ?>
   </ul>
<?php endif; ?>