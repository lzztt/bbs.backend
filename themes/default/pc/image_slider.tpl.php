<div id='coin-slider'>
  <?php foreach ($images as $i): ?>
    <a href="/node/<?php echo $i['nid']; ?>">
    <img src="<?php echo $i['path']; ?>" />
    <span>
      <?php echo $i['name'] . ' @ ' . $i['title']; ?>
    </span>
    </a>
  <?php endforeach; ?>
</div>