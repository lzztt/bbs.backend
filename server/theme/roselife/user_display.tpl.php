<?= $userLinks ?>
<div>
  <figure>
    <img class="avatar" src="<?= $avatar ?>" alt="<?= $username ?>的头像">
    <figcaption><?= $username ?></figcaption>
    <?php if ($pm): ?>
      <a href="#sendPM" class="button popup" data-vars='{"uid":<?= $uid ?>,"username":"<?= $username ?>"}'>发送站内短信</a>
    <?php endif ?>
  </figure>
  <dl>
    <?php foreach ($info as $k => $v): ?>
      <dt><?= $k ?></dt><dd><?= $v ?></dd>
    <?php endforeach ?>
  </dl>
</div>
<table class='user_topics'>
  <caption>最近发表的论坛话题</caption>
  <thead><tr><th>论坛话题</th><th>发表时间</th></tr></thead>
  <tbody class="even_odd_parent">
    <?php foreach ($topics as $t): ?>
      <tr>
        <td><a href="/node/<?= $t['nid'] ?>"><?= $t['title'] ?></a></td><td><?= \date('m/d/Y H:i', $t['createTime']) ?></td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>
<table class='user_topics'>
  <caption>最近回复的论坛话题</caption>
  <thead><tr><th>论坛话题</th><th>发表时间</th></tr></thead>
  <tbody class="even_odd_parent">
    <?php foreach ($comments as $c): ?>
      <tr>
        <td><a href="/node/<?= $c['nid'] ?>"><?= $c['title'] ?></a></td><td><?= \date('m/d/Y H:i', $c['createTime']) ?></td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>