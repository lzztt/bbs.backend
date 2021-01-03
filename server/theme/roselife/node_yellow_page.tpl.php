<?php

use lzx\html\Template;

function (
  string $ajaxUri,
  Template $breadcrumb,
  int $commentCount,
  int $nid,
  array $node,
  Template $pager,
  array $comments
) {
?>

  <header class="content_header">
    <?= $breadcrumb ?>
    <span class='v_guest'>您需要先<a onclick="window.app.login()" style="cursor: pointer">登录</a>才能发表新话题或回复</span>
    <button type="button" class='v_user' onclick="window.app.openCommentEditor({nodeId: <?= $nid ?>})">回复</button>
    <span class="ajax_load" data-ajax='<?= $ajaxUri ?>'><?= $commentCount ?> replies, <span class="ajax_viewCount<?= $nid ?>"></span> views</span>
    <?= $pager ?>
  </header>

  <article>
    <style>
      .bcard {
        max-width: min(100vw, 400px);
        margin: 0.5rem;
        border: 1px solid #006666;
      }

      .bcard div {
        display: grid;
        grid-template-columns: 4.5rem auto;
        grid-gap: 0.25rem;
      }

      .bcard div span:nth-child(odd) {
        text-align: right;
        color: #006666;
      }
    </style>
    <div class="bcard">
      <div>
        <span>地址</span><span><?= $node['address'] ?></span>
        <span>电话</span><span><?= $node['phone'] ?></span>
        <?php if (isset($node['fax'])) : ?>
          <span>传真</span><span><?= $node['fax'] ?></span>
        <?php endif ?>
        <?php if (isset($node['email'])) : ?>
          <span>电子邮箱</span><span><?= $node['email'] ?></span>
        <?php endif ?>
        <?php if (isset($node['website'])) : ?>
          <span>网站</span><span><?= $node['website'] ?></span>
        <?php endif ?>
      </div>
    </div>
    <?php if (!empty($node['HTMLbody'])) : ?>
      <div class="article_content">
        <div class="markdown"><?= $node['HTMLbody'] ?></div>
        <?= $node['attachments'] ?>
      </div>
    <?php endif ?>
  </article>

  <?php if ($comments) : ?>
    <div class="comments-node-type-yp" id="comments">
      <h2 id="comments-title">评论</h2>
      <?php foreach ($comments as $index => $c) : ?>
        <a id="comment<?= $c['id'] ?>"></a>
        <article>
          <header>
            <a onclick="window.app.user(<?= $c['uid'] ?>)"><?= $c['username'] ?></a>
            <time data-time="<?= $c['createTime'] ?>" data-method="toAutoTime"></time>
          </header>

          <div class="article_content markdown"><?= $c['HTMLbody'] ?></div>

          <footer>
            <div class="v_user actions">
              <?php $urole = 'v_user_superadm' . ' v_user_' . $c['uid'] ?>
              <script>
                const editJson_<?= $c['id'] ?> = <?= $c["editJson"] ?>;
                const quoteJson_<?= $c['id'] ?> = <?= $c["quoteJson"] ?>;
              </script>
              <button type="button" class="<?= $urole ?>" onclick="window.app.openCommentEditor(editJson_<?= $c['id'] ?>)">编辑</button>
              <button type="button" class="<?= $urole ?>" onclick="window.app.delete('comment', <?= $c['id'] ?>)">删除</button>
              <button type="button" onclick="window.app.openCommentEditor({nodeId: <?= $nid ?>})">回复</button>
              <button type="button" onclick="window.app.openCommentEditor(quoteJson_<?= $c['id'] ?>)">引用</button>
            </div>
          </footer>
        </article>
      <?php endforeach ?>
    </div>
  <?php endif ?>

  <?= $pager ?>

<?php
};
