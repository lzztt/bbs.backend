<?php

use lzx\html\Template;

function (
  string $data,
  Template $pager
) {
?>

  <header class="content_header">
    <h1 class="title">近期活动</h1>
    <div><a href="/help#activity">如何发布活动</a></div>
    <?= $pager ?>
  </header>
  <section class='even_odd_parent activities'><?= $data ?></section>
  <?= $pager ?>

<?php
};
