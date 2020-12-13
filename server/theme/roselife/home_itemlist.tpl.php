<?php
function (
  int $lastModifiedTime,
  array $data
) {
?>

  <!-- <?= $lastModifiedTime ?> -->
  <div class="even_odd_parent">
    <?php foreach ($data as $n) : ?>
      <div <?= array_key_exists('class', $n) ? ('class="' . $n['class'] . '"') : '' ?>>
        <a href="<?= $n['uri'] ?>"><?= $n['text'] ?></a>
        <?php if (array_key_exists('time', $n)) : ?>
          <time data-time="<?= $n['time'] ?>" data-method="<?= $n['method'] ?>"></time>
        <?php else : ?>
          <small><?= $n['after'] ?></small>
        <?php endif; ?>
      </div>
    <?php endforeach ?>
  </div>

<?php
};
