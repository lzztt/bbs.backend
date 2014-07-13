<div id="coin-slider">
   <?php foreach ($images as $i): ?>
      <a style="display: none;" href="/node/<?php print $i['nid']; ?>">
         <img src="/img.png" data-src="<?php print $i['path']; ?>" alt="<?php print $i['name'] . ' @ ' . $i['title']; ?>" />
      </a>
   <?php endforeach; ?>
</div>