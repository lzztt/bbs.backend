<?php print $userLinks; ?>
<form accept-charset="UTF-8" autocomplete="off" method="post" action="<?php print $action; ?>">
   <fieldset>
      <label class="label oldpassword">旧密码</label><input name="password_old" type="password" required="required">
   </fieldset> 
   <fieldset>
      <label class="label">新密码</label><input name="password_new" type="password" required="required">
   </fieldset>
   <fieldset>
      <label class="label">确认新密码</label><input name="password_new2" type="password" required="required">
   </fieldset>
   <fieldset>
      <button type="submit">更改密码</button>
   </fieldset>
</form>