<?php

use lzx\html\Template;

function (
  string $ajaxUri,
  array $nodes,
  Template $pager,
  int $tid
) {
?>

  <header class='content_header'>
    <div>
      <span class='v_guest'>您需要先<a onclick="window.app.login()" style="cursor: pointer">登录</a>或<a onclick="window.app.register()" style="cursor: pointer">注册</a>才能发表新话题</span>
      <button type="button" class='v_user' onclick="window.app.openNodeEditor({tagId: <?= $tid ?>})">发表新话题</button>
      <?= $pager ?>
    </div>
  </header>
  <?php if (isset($nodes)) : ?>
    <table class="forum-topics">
      <thead>
        <tr>
          <th>主题</th>
          <th>作者</th>
          <th>最后回复</th>
          <th>回复 / 浏览</th>
        </tr>
      </thead>

      <tbody class='even_odd_parent ajax_load' data-ajax='<?= $ajaxUri ?>'>
        <?php foreach ($nodes as $node) : ?>
          <tr class="<?= ($node['weight'] >= 2) ? 'topic-sticky' : '' ?>">
            <td><a href="/node/<?= $node['id'] ?>"><?= $node['title'] ?></a></td>
            <td><?= $node['creater_name'] ?> <span class='time'><?= ($node['create_time']) ?></span></td>
            <td><?php if ($node['comment_count'] > 0) : ?><?= $node['commenter_name'] ?> <span class='time'><?= ($node['comment_time']) ?></span><?php endif ?></td>
            <td><?= $node['comment_count'] ?> / <span class="ajax_viewCount<?= $node['id'] ?>"></span></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  <?php endif ?>

  <?= $pager ?>

<?php
};
