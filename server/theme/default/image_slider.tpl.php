<div id="coin-slider">
   <?php foreach ($images as $i): ?>
      <a style="display: none;" href="/node/<?php echo $i['nid']; ?>">
         <img src="/img.png" data-src="<?php echo $i['path']; ?>" alt="<?php echo $i['name'] . ' @ ' . $i['title']; ?>" />
      </a>
   <?php endforeach; ?>
</div>