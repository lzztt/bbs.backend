<div id="activities">
  <?php foreach ($activities as $a): ?>
    <div class="act_stat">
      <h3><?= $a['name'] ?> <small><?= \date('Y年 n月 j日', $a['time']) ?> <a href="/node/<?= $a['nid'] ?>">论坛讨论帖</a></small></h3>
      <div class="charts"><?= $a['chart'] ?></div>
    </div>
    <?= $a['comments'] ?>
  <?php endforeach ?>
</div>
