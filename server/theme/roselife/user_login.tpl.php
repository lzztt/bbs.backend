<?php print $userLinks; ?>
<form accept-charset="UTF-8" autocomplete="off" method="post" action="/user/login">
   <fieldset>
      <label class='label' data-help="输入您在 缤纷休斯顿 华人论坛 的用户名">用户名</label><input name="username" type="text" required autofocus>
   </fieldset>
   <fieldset>
      <label class='label' data-help="输入与您用户名相匹配的密码">密码</label><input name="password" type="password" required>
   </fieldset>
   <fieldset>
      <button type="submit">登录</button>
   </fieldset>
</form>