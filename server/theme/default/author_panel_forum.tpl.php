<div class="author-pane">
  <div class="author-pane-inner">
    <div class="picture">
      <a title="浏览用户信息" href="/user/<?= $uid ?>"><img width="120" height="120" title="<?= $username ?> 的头像" alt="<?= $username ?> 的头像" src="<?= $avatar ?>" /><br /><?= $username ?></a>
    </div>

    <div class="author-pane-line author-joined">
      <span class="author-pane-label">性别:</span> <?= $sex ?>
    </div>
    <div class="author-pane-line author-joined">
      <span class="author-pane-label">城市:</span> <?= $city ?>
    </div>
    <div class="author-pane-line author-joined">
      <span class="author-pane-label">加入:</span> <?= $joinTime ?>
    </div>
    <div class="author-pane-line author-points">
      <span class="author-pane-label">金币</span>: <?= $points ?>
    </div>

    <div class="author-pane-icon">
      <a href="/user/<?= $uid ?>/pm"><img title="发送站内短信" alt="发送站内短信" src="/themes/default/images/forum/pm.gif"></a>
    </div>
  </div>
</div>