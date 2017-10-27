<div id="coin-slider">
  <?php foreach ($images as $i): ?>
    <a style="display: none;" href="/node/<?= $i['nid'] ?>">
      <img src="/img.png" data-src="<?= $i['path'] ?>" alt="<?= $i['name'] . ' @ ' . $i['title'] ?>" />
    </a>
  <?php endforeach ?>
</div>