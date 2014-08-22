<?php print $userLinks; ?>
<form accept-charset="UTF-8" autocomplete="off" method="post">
   <fieldset>
      <label class='label' data-help="输入您的用户名">用户名</label><input name="username" type="text" required  autofocus>
   </fieldset>
   <fieldset>
      <label class='label' data-help="输入您注册时使用的电子邮箱地址">注册电子邮箱</label><input name="email" type="email" required>
   </fieldset>
   <fieldset>
      <button type="submit">发送重设密码链接</button>
   </fieldset>
</form>