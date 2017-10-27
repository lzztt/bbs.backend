<?= $userLinks ?>
<?= $mailBoxLinks ?>
<ul class="pm_thread js_even_odd_parent">
  <?php foreach ($msgs as $m): ?>
    <li>
      <div class="pm_avatar"><a href="/user/<?= $m['uid'] ?>"><img alt="<?= $m['username'] ?>的头像" src="<?= $m['avatar'] ?>"></a></div>
      <div class="pm_info"><a href="/user/<?= $m['uid'] ?>"><?= $m['username'] ?></a><br><?= $m['time'] ?></div>
      <div class="pm_body"><?= $m['body'] ?>
        <div class="ed_actions"><a class="button" href="/pm/<?= $topicID ?>/delete/<?= $m['id'] ?>"><?= ($m['id'] == $topicID ? '删除话题' : '删除') ?></a></div>
      </div>
    </li>
  <?php endforeach ?>
</ul>
<form accept-charset="UTF-8" autocomplete="off" method="post" action="/pm/<?= $topicID ?>/reply" id="pm_reply">
  <div class="form_element">
    <label>收信人</label><input size="22" readonly="readonly" name="to" type="text" value="<?= $replyTo['username'] ?>">
  </div>
  <div class="form_element">
    <label data-help="最少5个字母或3个汉字">回复内容</label><textarea rows="5" cols="50" name="body" required="required"></textarea>
  </div>
  <input name="fromUID" value="<?= $fromUID ?>" type="hidden">
  <input name="toUID" value="<?= $replyTo['id'] ?>" type="hidden">
  <div class="form_element"><button type="submit">发送</button></div>
</form>