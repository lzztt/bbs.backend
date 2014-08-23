<header class='content_header'>
   <?php print $userLinks; ?>
   <?php print $pager; ?>
</header>
<ul class='even_odd_parent'>
   <?php foreach ( $nodes as $n ): ?>
      <li><a href="/node/<?php print $n[ 'id' ]; ?>"><?php print $n[ 'title' ]; ?></a></li>
   <?php endforeach; ?>
</ul>
<?php print $pager; ?>
