<?php

use lzx\html\Template;

function (
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
    <style>
      .topic_list > div > span:first-child > span {
        padding-left: 0.25rem;
        min-width: min-content;
      }
    </style>
    <div class='topic_list even_odd_parent'>
      <?php foreach ($nodes as $node) : ?>
        <div <?= ($node['weight'] >= 2) ? 'class="topic-sticky"' : '' ?>>
          <span>
            <?php if ($node['weight'] >= 2) : ?>
              <svg class="MuiSvgIcon-root" focusable="false" viewBox="0 0 24 24" aria-hidden="true" style="color: #2962ff;">
                <path d="M17 3H7c-1.1 0-1.99.9-1.99 2L5 21l7-3 7 3V5c0-1.1-.9-2-2-2zm0 15l-5-2.18L7 18V5h10v13z"></path>
              </svg>
            <?php else : ?>
              <svg class="MuiSvgIcon-root" focusable="false" viewBox="0 0 24 24" aria-hidden="true" style="color: #2962ff;">
                <circle cx="15.5" cy="9.5" r="1.5"></circle>
                <circle cx="8.5" cy="9.5" r="1.5"></circle>
                <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm0-4c-.73 0-1.38-.18-1.96-.52-.12.14-.86.98-1.01 1.15.86.55 1.87.87 2.97.87 1.11 0 2.12-.33 2.98-.88-.97-1.09-.01-.02-1.01-1.15-.59.35-1.24.53-1.97.53z"></path>
              </svg>
            <?php endif ?>
            <a href="/node/<?= $node['id'] ?>"><?= $node['title'] ?></a>
            <?php if ($node['comment_count'] > 0) : ?>
              <span><?= $node['comment_count'] ?></span>
            <?php endif ?>
          </span>
          <span><?= $node['creater_name'] ?></span>
          <span class='time' data-time="<?= $node['create_time'] ?>"></span>
        </div>
      <?php endforeach ?>
    </div>
  <?php endif ?>

  <div>
    <span class='v_guest'>您需要先<a onclick="window.app.login()" style="cursor: pointer">登录</a>或<a onclick="window.app.register()" style="cursor: pointer">注册</a>才能发表新话题</span>
    <button type="button" class='v_user' onclick="window.app.openNodeEditor({tagId: <?= $tid ?>})">发表新话题</button>
    <?= $pager ?>
  </div>

<?php
};
