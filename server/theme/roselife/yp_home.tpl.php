<?php
function (
  int $tid,
  array $yp
) {
?>

  <a id='yp_join' href="/yp/join">加入黄页</a>

  <ul class='even_odd_parent' id='yp_list'>
    <?php foreach ($yp[$tid]['children'] as $groupID) : ?>
      <li class="l1">
        <a title="<?= $yp[$groupID]['description'] ?>" href="/yp/<?= $yp[$groupID]['id'] ?>"><?= $yp[$groupID]['name'] ?></a>
        <ul>
          <?php foreach ($yp[$groupID]['children'] as $tagID) : ?>
            <li class="l2"><a title="<?= $yp[$tagID]['description'] ?>" href="/yp/<?= $yp[$tagID]['id'] ?>"><?= $yp[$tagID]['name'] ?></a></li>
          <?php endforeach ?>
        </ul>
      </li>
    <?php endforeach ?>
  </ul>

<?php
};
