<?php if ($images): ?>
  <ul style="display: none;">
    <?php foreach ($images as $i): ?>
      <li data-href="/node/<?= $i['nid'] ?>" data-img="<?= $i['path'] ?>"><?= $i['name'] . ' @ ' . $i['title'] ?></li>
    <?php endforeach ?>
  </ul>
<?php endif ?>