<?php

use lzx\html\Template;
use site\City;

function (
  int $city,
  string $ajaxUri,
  Template $breadcrumb,
  int $commentCount,
  int $nid,
  Template $pager,
  int $postNumStart,
  array $posts,
  int $tid
) {
?>

  <header class="content_header">
    <?= $breadcrumb ?>
    <span class='v_guest'>您需要先<a onclick="window.app.login()" style="cursor: pointer">登录</a>或<a onclick="window.app.register()" style="cursor: pointer">注册</a>才能发表新话题或回复</span>
    <button type="button" class='v_user' onclick="window.app.openNodeEditor({tagId: <?= $tid ?>})">发表新话题</button>
    <button type="button" class='v_user' onclick="window.app.openCommentEditor({nodeId: <?= $nid ?>})">回复</button>
    <button type="button" class='v_user' onclick="fetch('/node/<?= $nid ?>/bookmark').then(() => {alert('帖子成功加入到您的收藏夹中！')})">收藏</button>
    <span class="ajax_load" data-ajax='<?= $ajaxUri ?>'><?= $commentCount ?> replies, <span class="ajax_viewCount<?= $nid ?>"></span> views</span>
    <?= $pager ?>
  </header>


  <?php foreach ($posts as $index => $p) : ?>
    <div class='forum_post'>
      <a id="<?= $p['type'] . $p['id'] ?>"></a>
      <?= $p['authorPanel'] ?>
      <article>
        <header>
          <a onclick="window.app.user(<?= $p['uid'] ?>)"><?= $p['username'] ?></a> <span class='city'><?= $p['city'] ?></span>
          <span class='time'><?= $p['createTime'] . (empty($p['lastModifiedTime']) ? '' : ' (修改于 ' . $p['lastModifiedTime'] . ')') ?></span>
          <?php if ($p['type'] == 'comment') : ?>
            <span class="comment_num">#<?= $postNumStart + $index ?></span>
          <?php endif ?>
        </header>

        <div class="article_content">
          <?php if ($index === 0 && ($city === City::HOUSTON || $city === City::DALLAS)) : ?>
            <style>
              .responsive-ad {
                display: inline-block;
                float: right;
                width: 300px;
                height: 250px;
              }

              @media(max-width: 767px) {
                .responsive-ad {
                  display: none
                }
              }
            </style>
            <!-- responsive_ad -->
            <ins class="adsbygoogle responsive-ad" data-ad-client="ca-pub-8257334386742604" data-ad-slot="<?= $city === City::HOUSTON ? '1050744881' : '4245946485' ?>"></ins>
            <script>
              (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
          <?php endif ?>
          <?= $p['HTMLbody'] . $p['attachments'] ?>
        </div>

        <footer class='v_user'>
          <div class="actions">
            <?php $urole = 'v_user_superadm v_user_tagadm_' . $tid . ' v_user_' . $p['uid'] ?>
            <?php if (!empty($p['report'])) : ?>
              <button type="button" class="report" onclick="window.app.report(<?= $nid ?>)">举报</button>
            <?php endif ?>
            <?php if ($tid == 16 && $p['type'] == 'node') : ?>
              <a class="button <?= $urole ?>" href="/node/<?= $p['id'] ?>/activity" rel="nofollow">发布为活动</a>
            <?php endif ?>
            <script>
              const editJson_<?= $p['id'] ?> = <?= $p["editJson"] ?>;
              const quoteJson_<?= $p['id'] ?> = <?= $p["quoteJson"] ?>;
            </script>
            <button type="button" class="edit <?= $urole ?>" onclick="window.app.open<?= $p['type'] === 'node' ? 'Node' : 'Comment' ?>Editor(editJson_<?= $p['id'] ?>)">编辑</button>
            <button type="button" class="delete <?= $urole ?>" onclick="window.app.delete('<?= $p['type'] ?>', <?= $p['id'] ?>)">删除</button>
            <button type="button" class="reply" onclick="window.app.openCommentEditor({nodeId: <?= $nid ?>})">回复</button>
            <button type="button" class="quote" onclick="window.app.openCommentEditor(quoteJson_<?= $p['id'] ?>)">引用</button>
          </div>
        </footer>
      </article>
    </div>
  <?php endforeach ?>

  <?= $pager ?>

<?php
};
