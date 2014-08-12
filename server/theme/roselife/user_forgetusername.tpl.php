<?php print $userLinks; ?>
<form accept-charset="UTF-8" autocomplete="off" method="post" action="/user/username">
   <fieldset>
      <label class='label' data-help="输入您注册时使用的电子邮箱地址">注册电子邮箱地址</label><input size="22" name="email" type="email" required="required">
   </fieldset>
   <fieldset>
      <button type="submit">发送您的用户名</button>
   </fieldset>
</form>