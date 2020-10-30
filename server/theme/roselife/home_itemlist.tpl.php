<?php
function (
  array $data
) {
?>

  <div class="even_odd_parent">
    <?php foreach ($data as $n) : ?>
      <div <?= array_key_exists('class', $n) ? ('class="' . $n['class'] . '"') : '' ?>>
        <a href="<?= $n['uri'] ?>"><?= $n['text'] ?></a>
        <?php if (array_key_exists('time', $n)) : ?>
          <span data-time="<?= $n['time'] ?>" data-method="<?= $n['method'] ?>"></span>
        <?php else : ?>
          <span><?= $n['after'] ?></span>
        <?php endif; ?>
      </div>
    <?php endforeach ?>
  </div>

<?php
};
