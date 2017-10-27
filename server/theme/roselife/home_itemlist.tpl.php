<ul class="even_odd_parent">
  <?php foreach ($data as $n): ?>
    <li <?= \array_key_exists('class', $n) ? ('class="' . $n['class'] . '"') : '' ?> data-after='<?= $n['after'] ?>'><a href="<?= $n['uri'] ?>"><?= $n['text'] ?></a></li>
<?php endforeach ?>
</ul>
