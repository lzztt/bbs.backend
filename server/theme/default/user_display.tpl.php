<?= $userLinks ?>
<div>
  <div>
    <img class="avatar" src="/data/avatars/1-100.png" alt="HoustonBBS的头像">
    <?= $username ?><br />
    <?php if ($pm): ?>
      <a class="button" href="<?= $pm ?>">发送站内短信</a>
    <?php endif ?>
  </div>

  <dl>
    <?php foreach ($info as $k => $v): ?>
      <dt><?= $k ?></dt><dd><?= $v ?></dd>
    <?php endforeach ?>
  </dl>
</div>
<table>
  <caption>最近发表的论坛话题</caption>
  <thead><tr><th>论坛话题</th><th>发表时间</th></tr></thead>
  <tbody class="js_even_odd_parent">
    <?php foreach ($topics as $t): ?>
      <tr class="even">
        <td><a href="/node/<?= $t['nid'] ?>"><?= $t['title'] ?></a></td><td><?= \date('m/d/Y H:i', $t['create_time']) ?></td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>
<table>
  <caption>最近发表的论坛话题</caption>
  <thead><tr><th>论坛话题</th><th>发表时间</th></tr></thead>
  <tbody class="js_even_odd_parent">
    <?php foreach ($comments as $c): ?>
      <tr class="even">
        <td><a href="/node/<?= $c['nid'] ?>"><?= $c['title'] ?></a></td><td><?= \date('m/d/Y H:i', $c['create_time']) ?></td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>