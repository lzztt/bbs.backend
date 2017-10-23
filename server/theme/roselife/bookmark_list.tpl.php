<header class='content_header'>
  <?php print $userLinks; ?>
  <button type="button" class='v_user edit_bookmark'>编辑</button>
  <?php print $pager; ?>
</header>
<ul class='bookmarks even_odd_parent'>
  <?php foreach ( $nodes as $n ): ?>
    <li><button type="button" style='display: none;' class='delete_bookmark' data-nid='<?php print $n['id']; ?>'>删除</button><a href="/node/<?php print $n['id']; ?>"><?php print $n['title']; ?></a></li>
  <?php endforeach; ?>
</ul>
<?php print $pager; ?>
