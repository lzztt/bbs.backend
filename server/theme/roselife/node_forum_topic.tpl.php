<header class="content_header">
  <?= $breadcrumb ?>
  <span class='v_guest'>您需要先<a class='popup' href="#login">登录</a>或<a href="/app/user/register">注册</a>才能发表新话题或回复</span>
  <button type="button" class='v_user create_node' data-action="/forum/<?= $tid ?>/node">发表新话题</button>
  <button type="button" class='v_user reply' data-action="/node/<?= $nid ?>/comment">回复</button>
  <button type="button" class='v_user bookmark' data-action="/node/<?= $nid ?>/bookmark">收藏</button>
  <span class="ajax_load" data-ajax='<?= $ajaxURI ?>'><?= $commentCount ?> replies, <span class="ajax_viewCount<?= $nid ?>"></span> views</span>
  <?= $pager ?>
</header>


<?php foreach ($posts as $index => $p): ?>
  <div class='forum_post'>
    <a id="<?= $p['type'] . $p['id'] ?>"></a>
    <?= $p['authorPanel'] ?>
    <article>
      <header>
        <a href="/app/user/<?= $p['uid'] ?>"><?= $p['username'] ?></a> <span class='city'><?= $p['city'] ?></span>
        <span class='time'><?= $p['createTime'] . (empty($p['lastModifiedTime']) ? '' : ' (修改于 ' . $p['lastModifiedTime'] . ')') ?></span>
        <?php if ($p['type'] == 'comment'): ?>
          <span class="comment_num">#<?= $postNumStart + $index ?></span>
        <?php endif ?>
      </header>

      <div class="article_content">
        <?php if ($index == 0): ?>
          <style>
            .responsive-ad { display:inline-block; float:right; width:300px; height:250px; }
            @media(max-width: 767px) { .responsive-ad { display:none } }
          </style>
          <!-- responsive_ad -->
          <ins class="adsbygoogle responsive-ad"
              data-ad-client="ca-pub-8257334386742604"
              data-ad-slot="1050744881"></ins>
          <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
          </script>
        <?php endif ?>
        <?= $p['HTMLbody'] . $p['attachments'] ?>
      </div>

      <footer class='v_user'>
        <div class="actions">
          <?php $urole = 'v_user_superadm v_user_tagadm_' . $tid . ' v_user_' . $p['uid'] ?>
          <?php if (!empty($p['report'])): ?>
            <button type="button" class="report" data-action="nid=<?= $nid ?>&uid=<?= $p['uid'] ?>">举报</button>
          <?php endif ?>
          <?php if ($tid == 16 && $p['type'] == 'node'): ?>
            <a class="button <?= $urole ?>" href="/node/<?= $p['id'] ?>/activity" rel="nofollow">发布为活动</a>
          <?php endif ?>

          <button type="button" class="edit <?= $urole ?>" data-raw="#<?= $p['type'] . '_' . $p['id'] ?>_raw" data-action="<?= '/' . $p['type'] . '/' . $p['id'] . '/edit' ?>">编辑</button>
          <button type="button" class="delete <?= $urole ?>" data-action="<?= '/' . $p['type'] . '/' . $p['id'] . '/delete' ?>">删除</button>
          <button type="button" class="reply" data-action="/node/<?= $nid ?>/comment">回复</button>
          <button type="button" class="quote" data-raw="#<?= $p['type'] . '_' . $p['id'] ?>_raw" data-action="/node/<?= $nid ?>/comment">引用</button>
        </div>
        <div id="<?= $p['type'] . '_' . $p['id'] ?>_raw" style="display:none;">
          <pre class='username'><?= $p['username'] ?></pre>
          <pre class="body"><?= $p['body'] ?></pre>
          <pre class="files"><?= $p['filesJSON'] ?></pre>
        </div>
      </footer>
    </article>
  </div>
<?php endforeach ?>

<?= $pager ?>
<?= $editor ?>
