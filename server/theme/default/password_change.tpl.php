<?php print $userLinks; ?>
<form accept-charset="UTF-8" autocomplete="off" method="post" action="/password/change" id="user-pass">
   <div class="form_element">
      <label>旧密码</label><input size="22" name="password_old" type="password" required="required">
   </div>
   <div class="form_element">
      <label>新密码</label><input size="22" name="password_new" type="password" required="required">
   </div>
   <div class="form_element">
      <label>请重新输入新密码</label><input size="22" name="password_new2" type="password" required="required">
   </div>
   <div class="form_element">
      <button type="submit">更改密码</button>
   </div>
</form>