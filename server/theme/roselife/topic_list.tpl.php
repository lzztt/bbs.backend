<header class='content_header'>
  <?php print $breadcrumb; ?>
  <div><?php print $boardDescription; ?></div>
  <div>
    <span class='v_guest'>您需要先<a class='popup' href="#login">登录</a>或<a href="/app/user/register">注册</a>才能发表新话题</span>
    <button type="button" class='v_user create_node' data-action="/forum/<?php print $tid; ?>/node">发表新话题</button>
    <?php print $pager; ?>
  </div>
</header>
<?php if ( isset( $nodes ) ): ?>
  <table class="forum-topics">
    <thead>
      <tr>
        <th>主题</th>
        <th>作者</th>
        <th>最后回复</th>
        <th>回复 / 浏览</th>
      </tr>
    </thead>

    <tbody class='even_odd_parent ajax_load' data-ajax='<?php print $ajaxURI; ?>'>
      <?php foreach ( $nodes as $node ): ?>
        <tr class="<?php print ($node['weight'] >= 2) ? 'topic-sticky' : ''; ?>">
          <td><a href="/node/<?php print $node['id']; ?>"><?php print $node['title']; ?></a></td>
          <td><?php print $node['creater_name']; ?> <span class='time'><?php print ($node['create_time'] ); ?></span></td>
          <td><?php if ( $node['comment_count'] > 0 ): ?><?php print $node['commenter_name']; ?> <span class='time'><?php print ($node['comment_time'] ); ?></span><?php endif; ?></td>
          <td><?php print $node['comment_count']; ?> / <span class="ajax_viewCount<?php print $node['id']; ?>"></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php print $editor; ?>
<?php print $pager; ?>

