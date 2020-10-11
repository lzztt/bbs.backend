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
      <a title="浏览用户信息" href="/user/<?= $uid ?>">
        <?php if ($avatar) : ?>
          <div class="avatar_circle" style="border: 0;">
            <img width="128" height="128" title="<?= $username ?> 的头像" alt="<?= $username ?> 的头像" src="<?= $avatar ?>">
          </div>
        <?php else : ?>
          <div class="avatar_circle">
            <?= mb_substr($username, 0, (preg_match('/^[A-Za-z0-9]{3}/', $username) ? 3 : 2)) ?>
          </div>
        <?php endif ?>
      </a>
    </div>
    <div>
      <span>加入:</span> <?= $joinTime ?>
    </div>
    <div>
      <span>声望:</span> <?= $points ?>
    </div>
    <div>
      <button onclick='window.app.openMsgEditor({toUser: {id:<?= $uid ?>,username:"<?= $username ?>"}})'>
        发短信
      </button>
    </div>
  </aside>

<?php
};
