<?php
function (
  array $data
) {
?>

  <div>
    <?php foreach ($data as $n) : ?>
      <a href="<?= $n['uri'] ?>"><?= $n['text'] ?></a><span><?= $n['after'] ?></span>
    <?php endforeach ?>
  </div>

<?php
};
