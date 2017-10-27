<?= $userLinks ?>
<?= $mailBoxLinks ?>
<?php foreach ($msgs as $m): ?>
  <article>
    <header>
      <a href="/user/<?= $m['uid'] ?>"><?= $m['username'] ?></a> <?= $m['time'] ?>
    </header>

    <div class="article_content"><?= $m['body'] ?>
      <a class="button" href="/pm/<?= $topicID ?>/delete/<?= $m['id'] ?>"><?= ($m['id'] == $topicID ? '删除短信' : '删除') ?></a>
    </div>
  </article>
<?php endforeach ?>

<form accept-charset="UTF-8" autocomplete="off" method="post" action="/pm/<?= $topicID ?>/reply" id="pm_reply">
  <fieldset>
    <label class="label">收信人</label><input readonly="readonly" name="to" type="text" value="<?= $replyTo['username'] ?>">
  </fieldset>
  <fieldset>
    <label class="label" data-help="最少5个字母或3个汉字">回复内容</label><textarea  name="body" required></textarea>
  </fieldset>
  <input name="fromUID" value="<?= $fromUID ?>" type="hidden">
  <input name="toUID" value="<?= $replyTo['id'] ?>" type="hidden">
  <fieldset><button type="submit">发送</button></fieldset>
</form>