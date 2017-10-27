<header class='content_header'>
  <?= $breadcrumb ?>
  <div><?= $boardDescription ?></div>
  <div>
    <span class='v_guest'>您需要先<a class='popup' href="#login">登录</a>或<a href="/app/user/register">注册</a>才能发表新话题</span>
    <button type="button" class='v_user create_node' data-action="/forum/<?= $tid ?>/node">发表新话题</button>
    <?= $pager ?>
  </div>
</header>
<?php if (isset($nodes)): ?>
  <table class="forum-topics">
    <thead>
      <tr>
        <th>主题</th>
        <th>作者</th>
        <th>最后回复</th>
        <th>回复 / 浏览</th>
      </tr>
    </thead>

    <tbody class='even_odd_parent ajax_load' data-ajax='<?= $ajaxURI ?>'>
      <?php foreach ($nodes as $node): ?>
        <tr class="<?= ($node['weight'] >= 2) ? 'topic-sticky' : '' ?>">
          <td><a href="/node/<?= $node['id'] ?>"><?= $node['title'] ?></a></td>
          <td><?= $node['creater_name'] ?> <span class='time'><?= ($node['create_time']) ?></span></td>
          <td><?php if ($node['comment_count'] > 0): ?><?= $node['commenter_name'] ?> <span class='time'><?= ($node['comment_time']) ?></span><?php endif ?></td>
          <td><?= $node['comment_count'] ?> / <span class="ajax_viewCount<?= $node['id'] ?>"></span></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
<?php endif ?>

<?= $editor ?>
<?= $pager ?>

