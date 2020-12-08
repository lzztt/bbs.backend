<?php
function (
  string $avatar,
  int $uid,
  string $username
) {
?>

  <div style="text-align: center;">
    <?php if ($avatar) : ?>
      <img title="<?= $username ?> 的头像" alt="<?= $username ?> 的头像" src="<?= $avatar ?>" class="avatar_circle_responsive" style="border:0px; cursor:pointer;" onclick="window.app.user(<?= $uid ?>)">
    <?php else : ?>
      <div class="avatar_circle_responsive" style="cursor:pointer;" onclick="window.app.user(<?= $uid ?>)">
        <?= mb_substr($username, 0, (preg_match('/^[A-Za-z0-9]{3}/', $username) ? 3 : 2)) ?>
      </div>
    <?php endif ?>
    <div class="pm_icon">
      <svg class="MuiSvgIcon-root" focusable="false" viewBox="0 0 24 24" aria-hidden="true" style="color:#3f51b5; cursor:pointer;" onclick='window.app.openMsgEditor({toUser: {id:<?= $uid ?>,username:"<?= $username ?>"}})'>
        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V8l8 5 8-5v10zm-8-7L4 6h16l-8 5z"></path>
      </svg>
    </div>
  </div>

<?php
};
