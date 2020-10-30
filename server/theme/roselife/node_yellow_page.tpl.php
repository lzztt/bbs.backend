<?php

use lzx\html\Template;

function (
  string $ajaxUri,
  Template $breadcrumb,
  int $commentCount,
  int $nid,
  array $node,
  Template $pager,
  int $postNumStart,
  array $comments
) {
?>

  <header class="content_header">
    <?= $breadcrumb ?>
    <span class='v_guest'>您需要先<a onclick="window.app.login()" style="cursor: pointer">登录</a>或<a onclick="window.app.register()" style="cursor: pointer">注册</a>才能发表新话题或回复</span>
    <button type="button" class='v_user' onclick="window.app.openCommentEditor({nodeId: <?= $nid ?>})">回复</button>
    <span class="ajax_load" data-ajax='<?= $ajaxUri ?>'><?= $commentCount ?> replies, <span class="ajax_viewCount<?= $nid ?>"></span> views</span>
    <?= $pager ?>
  </header>

  <article>
    <style>
      .bcard {
        display: inline-block;
        width: 95%;
        max-width: min(100vw, 400px);
        margin: 0.5em;
        border: 1px solid #006666;
        vertical-align: top;
      }

      .bcard header {
        background-color: gold;
        text-align: center;
        padding: 0.2em;
      }

      .bcard [data-before] {
        position: relative;
        padding: 0.1em;
        padding-left: 5em;
      }

      .bcard [data-before]:before {
        content: attr(data-before);
        display: inline-block;
        position: absolute;
        left: 0;
        width: 4.5em;
        text-align: right;
        padding-right: 0.5em;
        color: #006666;
      }

      .bcard footer {
        text-align: right;
        font-size: 0.929em;
        color: gray;
      }
    </style>
    <div class="bcard">
      <div data-before='地址'><?= $node['address'] ?></div>
      <div data-before='电话'><?= $node['phone'] ?></div>
      <?php if (isset($node['fax'])) : ?>
        <div data-before='传真'><?= $node['fax'] ?></div>
      <?php endif ?>
      <?php if (isset($node['email'])) : ?>
        <div data-before='电子邮箱'><a href="mailto:<?= $node['email'] ?>"><?= $node['email'] ?></a></div>
      <?php endif ?>
      <?php if (isset($node['website'])) : ?>
        <div data-before='网站'><a href="<?= $node['website'] ?>" target="_blank"><?= $node['website'] ?></a></div>
      <?php endif ?>
    </div>
    <?php if (!empty($node['HTMLbody'])) : ?>
      <div class="article_content"><?= $node['HTMLbody'] . $node['attachments'] ?></div>
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
            <span class='time'><?= $c['createTime'] . ($c['lastModifiedTime'] ? ' (修改于 ' . $c['lastModifiedTime'] . ')' : '') ?></span>
            <?php if ($c['type'] == 'comment') : ?>
              <span class="comment_num">#<?= $postNumStart + $index ?></span>
            <?php endif ?>
          </header>

          <div class="article_content"><?= $c['HTMLbody'] ?></div>

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
