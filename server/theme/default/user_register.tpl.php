<ul class="tabs">
   <li><a href="/user/login">登录</a></li>
   <li class="active"><a href="/user/register">创建新帐号</a></li>
   <li><a href="/user/password">重设密码</a></li>
   <li><a href="/user/username">忘记用户名</a></li>
</ul>
<form accept-charset="UTF-8" autocomplete="off" method="post" action="/user/register">
   <section>
      <div class="form_element">
         <label data-help="允许空格，不允许&quot;.&quot;、“-”、“_”以外的其他符号">用户名</label><input size="22" name="username" type="text" required="required">
      </div>
      <div class="form_element">
         <label data-help="一个有效的电子邮件地址。帐号激活后的初始密码和所有本站发出的信件都将寄至此地址。电子邮件地址将不会被公开，仅当您想要接收新密码或通知时才会使用">电子邮箱</label><input size="22" name="email" type="email" required="required">
      </div>
      <div class="form_element">
         <label data-help="确认电子邮箱">确认电子邮箱</label><input size="22" name="email2" type="email" required="required">
      </div>
   </section>
   <section>
      <div class="form_element">
         <label>右边图片的内容是什么？</label><input size="22" name="captcha" type="text" required="required">
         <img id="captchaImage" title="图形验证" alt="图形验证未能正确显示，请刷新" src="<?php print $captcha; ?>">
         <a onclick="document.getElementById('captchaImage').setAttribute('src', '<?php print $captcha; ?>/' + Math.random().toString().slice(2));
               event.preventDefault();" href="#">看不清，换一张</a>
      </div>
   </section>
   <section>
      <div>
         <a href="/node/23200">网站使用规范</a><br>
         <a href="/term">免责声明</a>
      </div>
      <div class="form_element"><button type="submit">同意使用规范和免责声明，并创建新帐号</button></div>
   </section>
</form>