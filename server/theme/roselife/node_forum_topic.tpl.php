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

  <?php if ($city === City::HOUSTON || $city === City::DALLAS) : ?>
    <style>
      .adsbygoogle {
        display: none;
      }

      @media(min-width: 768px) {
        .adsbygoogle {
          display: block;
          float: right;
          width: 300px;
          height: 250px;
        }
      }
    </style>
    <!-- responsive_ad -->
    <ins class="adsbygoogle" data-ad-client="ca-pub-8257334386742604" data-ad-slot="<?= $city === City::HOUSTON ? '1050744881' : '4245946485' ?>"></ins>
    <script>
      (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
  <?php endif ?>
  <article class='message_list'>
    <?php foreach ($posts as $index => $p) : ?>
      <section>
        <?= $p['authorPanel'] ?>
        <div style="max-width: 85%; width: 85%;">
          <header>
            <a onclick="window.app.user(<?= $p['uid'] ?>)"><?= $p['username'] ?></a>
            <span class='city'><?= $p['city'] ?></span>
            <time data-time="<?= $p['createTime'] ?>" data-method="toAutoTime"></time>
          </header>

          <div class="article_content">
            <?= $p['HTMLbody'] . $p['attachments'] ?>
          </div>

          <footer>
            <div class="v_user actions">
              <?php $urole = 'v_user_superadm v_user_tagadm_' . $tid . ' v_user_' . $p['uid'] ?>
              <?php if (!empty($p['report'])) : ?>
                <button type="button" onclick="window.app.report(<?= $nid ?>)">举报</button>
              <?php endif ?>
              <?php if ($tid == 16 && $p['type'] == 'node') : ?>
                <button type="button" class="<?= $urole ?>" onclick="window.location.href='/node/<?= $p['id'] ?>/activity'">发布为活动</button>
              <?php endif ?>
              <script>
                const editJson_<?= $p['id'] ?> = <?= $p["editJson"] ?>;
                const quoteJson_<?= $p['id'] ?> = <?= $p["quoteJson"] ?>;
              </script>
              <button type="button" class="<?= $urole ?>" onclick="window.app.open<?= $p['type'] === 'node' ? 'Node' : 'Comment' ?>Editor(editJson_<?= $p['id'] ?>)">编辑</button>
              <button type="button" class="<?= $urole ?>" onclick="window.app.delete('<?= $p['type'] ?>', <?= $p['id'] ?>)">删除</button>
              <button type="button" onclick="window.app.openCommentEditor({nodeId: <?= $nid ?>})">回复</button>
              <button type="button" onclick="window.app.openCommentEditor(quoteJson_<?= $p['id'] ?>)">引用</button>
            </div>
          </footer>
        </div>
      </section>
    <?php endforeach ?>
  </article>

  <header class="content_header">
    <span class='v_guest'>您需要先<a onclick="window.app.login()" style="cursor: pointer">登录</a>或<a onclick="window.app.register()" style="cursor: pointer">注册</a>才能发表新话题或回复</span>
    <button type="button" class='v_user' onclick="window.app.openNodeEditor({tagId: <?= $tid ?>})">发表新话题</button>
    <button type="button" class='v_user' onclick="window.app.openCommentEditor({nodeId: <?= $nid ?>})">回复</button>
    <?= $pager ?>
  </header>

<?php
};
