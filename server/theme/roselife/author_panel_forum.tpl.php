<?php
function (
  string $avatar,
  string $city,
  string $joinTime,
  int $points,
  string $sex,
  int $uid,
  string $username
) {
?>

  <aside class="author_panel">
    <div class="picture">
      <a title="浏览用户信息" href="/app/user/<?= $uid ?>"><img width="120" height="120" title="<?= $username ?> 的头像" alt="<?= $username ?> 的头像" src="<?= $avatar ?>"><br><?= $username ?></a>
    </div>
    <div>
      <span>性别:</span> <?= $sex ?>
    </div>
    <div>
      <span>城市:</span> <?= $city ?>
    </div>
    <div>
      <span>加入:</span> <?= $joinTime ?>
    </div>
    <div>
      <span>声望:</span> <?= $points ?>
    </div>
    <div>
      <a href="#sendPM" class="popup" data-vars='{"uid":<?= $uid ?>,"username":"<?= $username ?>"}'><img title="发送站内短信" alt="发送站内短信" src="/themes/default/images/forum/pm.gif"></a>
    </div>
  </aside>

<?php
};
