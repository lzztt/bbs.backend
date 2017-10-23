<?php print $userLinks; ?>
<form accept-charset="UTF-8" method="post">
  <fieldset>
    <label class='label' data-help="允许空格，不允许&quot;.&quot;、“-”、“_”以外的其他符号">用户名</label><input name="username" type="text" required autofocus>
  </fieldset>
  <fieldset>
    <label class='label' data-help="一个有效的电子邮件地址。帐号激活后的初始密码和所有本站发出的信件都将寄至此地址。电子邮件地址将不会被公开，仅当您想要接收新密码或通知时才会使用">电子邮箱</label><input name="email" type="email" required>
  </fieldset>
  <fieldset>
    <label class='label' data-help="确认电子邮箱">确认电子邮箱</label><input name="email2" type="email" required>
  </fieldset>
  <fieldset>
    <label class='label'>右边图片的内容是什么？</label><input name="captcha" type="text" required>
    <img id="captchaImage" title="图形验证" alt="图形验证未能正确显示，请刷新" src="<?php print $captcha; ?>">
    <a onclick="document.getElementById('captchaImage').setAttribute('src', '<?php print $captcha; ?>' + Math.random().toString().slice(2));
        event.preventDefault();" href="#">看不清，换一张</a>
  </fieldset>
  <fieldset><a href="/node/23200">网站使用规范</a><br><a href="/term">免责声明</a></fieldset>
  <fieldset><button type="submit">同意使用规范和免责声明，并创建新帐号</button></fieldset>
</form>