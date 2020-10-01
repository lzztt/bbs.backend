<?php
function (
  string $avatar,
  string $joinTime,
  int $points,
  int $uid,
  string $username
) {
?>

  <aside class="author_panel">
    <div class="picture">
      <a title="浏览用户信息" href="/app/user/<?= $uid ?>">
        <div class="avatar_circle">
          <?php if ($avatar) : ?>
            <img width="128" height="128" title="<?= $username ?> 的头像" alt="<?= $username ?> 的头像" src="<?= $avatar ?>">
          <?php else : ?>
            <div><?= $username ?></div>
          <?php endif ?>
        </div>
      </a>
    </div>
    <div>
      <span>加入:</span> <?= $joinTime ?>
    </div>
    <div>
      <span>声望:</span> <?= $points ?>
    </div>
    <div>
      <a href="#sendPM" class="popup" data-vars='{"uid":<?= $uid ?>,"username":"<?= $username ?>"}'>
        <img title="发送站内短信" alt="发送站内短信" src="/themes/default/images/forum/pm.gif">
      </a>
    </div>
  </aside>

<?php
};
