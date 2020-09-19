<?php

use lzx\html\Template;
use site\City;

function (
  int $city,
  Template $forumMenu,
  Template $ypMenu
) {
?>

  <ul class="sf-menu">
    <li><a href="/">首页</a></li>
    <li><a href="/search">搜索</a></li>
    <?php if ($city === City::HOUSTON) : ?>
      <li><a href="/activity">活动</a></li>
    <?php endif ?>
    <li><a href="/forum">论坛</a>
      <ul style="display: none;">
        <li><a href="/help">论坛帮助</a></li>
        <?= $forumMenu ?>
      </ul>
    </li>
    <?php if ($city === City::HOUSTON) : ?>
      <li><a href="/yp" style="color:red;">黄页</a>
        <ul style="display: none;">
          <li><a href="/yp/join">加入黄页</a></li>
          <?= $ypMenu ?>
        </ul>
      </li>
    <?php endif ?>
  </ul>

<?php
};
