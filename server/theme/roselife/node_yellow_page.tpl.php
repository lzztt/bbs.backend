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
    <div class="bcard">
      <ul class='clean'>
        <li data-before='地址'><?= $node['address'] ?></li>
        <li data-before='电话'><?= $node['phone'] ?></li>
        <?php if (isset($node['fax'])) : ?>
          <li data-before='传真'><?= $node['fax'] ?></li>
        <?php endif ?>
        <?php if (isset($node['email'])) : ?>
          <li data-before='电子邮箱'><a href="mailto:<?= $node['email'] ?>"><?= $node['email'] ?></a></li>
        <?php endif ?>
        <?php if (isset($node['website'])) : ?>
          <li data-before='网站'><a href="<?= $node['website'] ?>" target="_blank"><?= $node['website'] ?></a></li>
        <?php endif ?>
      </ul>
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

          <footer class='v_user'>
            <div class="actions">
              <?php $urole = 'v_user_superadm' . ' v_user_' . $c['uid'] ?>
              <script>
                const editJson_<?= $c['id'] ?> = <?= $c["editJson"] ?>;
                const quoteJson_<?= $c['id'] ?> = <?= $c["quoteJson"] ?>;
              </script>
              <button type="button" class="edit <?= $urole ?>" onclick="window.app.openCommentEditor(editJson_<?= $c['id'] ?>)">编辑</button>
              <button type="button" class="delete <?= $urole ?>" onclick="window.app.delete('comment', <?= $c['id'] ?>)">删除</button>
              <button type="button" class="reply" onclick="window.app.openCommentEditor({nodeId: <?= $nid ?>})">回复</button>
              <button type="button" class="quote" onclick="window.app.openCommentEditor(quoteJson_<?= $c['id'] ?>)">引用</button>
            </div>
          </footer>
        </article>
      <?php endforeach ?>
    </div>
  <?php endif ?>

  <?= $pager ?>

<?php
};
