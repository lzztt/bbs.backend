<?= $userLinks ?>
<form accept-charset="UTF-8" autocomplete="off" method="post">
  <fieldset>
    <label class="label oldpassword">旧密码</label><input name="password_old" type="password" required autofocus>
  </fieldset>
  <fieldset>
    <label class="label">新密码</label><input name="password_new" type="password" required>
  </fieldset>
  <fieldset>
    <label class="label">确认新密码</label><input name="password_new2" type="password" required>
  </fieldset>
  <fieldset>
    <button type="submit">更改密码</button>
  </fieldset>
</form>