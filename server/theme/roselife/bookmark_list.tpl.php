<header class='content_header'>
  <?= $userLinks ?>
  <button type="button" class='v_user edit_bookmark'>编辑</button>
  <?= $pager ?>
</header>
<ul class='bookmarks even_odd_parent'>
  <?php foreach ($nodes as $n): ?>
    <li><button type="button" style='display: none;' class='delete_bookmark' data-nid='<?= $n['id'] ?>'>删除</button><a href="/node/<?= $n['id'] ?>"><?= $n['title'] ?></a></li>
  <?php endforeach ?>
</ul>
<?= $pager ?>
