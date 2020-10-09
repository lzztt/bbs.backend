<?php
function (
  array $data
) {
?>

  <ul class="even_odd_parent">
    <?php foreach ($data as $n) : ?>
      <li <?= array_key_exists('class', $n) ? ('class="' . $n['class'] . '"') : '' ?>>
        <a href="<?= $n['uri'] ?>"><?= $n['text'] ?></a>
        <span><?= $n['after'] ?></span>
      </li>
    <?php endforeach ?>
  </ul>

<?php
};
