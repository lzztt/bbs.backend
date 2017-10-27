<header class='content_header'>
  <?= $userLinks ?>
  <?= $mailBoxLinks ?>
  <?= $pager ?>
</header>
<table class='pm_list'>
  <thead>
    <tr><th>短信</th><th>联系人</th><th>时间</th></tr>
  </thead>
  <tbody class="even_odd_parent">
    <?php foreach ($msgs as $m): ?>
      <tr>
        <td><?php if ($m['isNew']): ?><span style="color:red;">new </span><?php endif ?><a href="/pm/<?= $m['mid'] ?>"><?= $m['body'] ?></a></td>
        <td><a href="/user/<?= $m['uid'] ?>"><?= $m['user'] ?></a></td>
        <td><?= $m['time'] ?></td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>
<?= $pager ?>