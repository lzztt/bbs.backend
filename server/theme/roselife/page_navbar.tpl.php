<?php

use lzx\html\Template;

function (
  Template $forumMenu
) {
?>

  <ul class="sf-menu">
    <li><a href="/">首页</a></li>
    <li><a href="/search">搜索</a></li>
    <li><a href="/forum">论坛</a>
      <ul style="display: none;">
        <li><a href="/help">论坛帮助</a></li>
        <?= $forumMenu ?>
      </ul>
    </li>
  </ul>

<?php
};
