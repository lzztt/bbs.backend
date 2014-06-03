<ul class="tabs">
   <li><a href="/user/login">登录</a></li>
   <li class="active"><a href="/user/register">创建新帐号</a></li>
   <li><a href="/user/password">重设密码</a></li>
   <li><a href="/user/username">忘记用户名</a></li>
</ul>
<form accept-charset="UTF-8" autocomplete="off" method="post" action="/user/register">
   <fieldset>
      <legend>帐户信息</legend>

      <div class="element_label">
         <label>用户名</label><span class="element_required"> * </span><span class="element_help" title="允许空格，不允许&quot;.&quot;、“-”、“_”以外的其他符号"> ? </span>
      </div>
      <div class="element_input">
         <input size="22" name="username" type="text" required="required">
      </div>

      <div class="element_label">
         <label>电子邮箱</label><span class="element_required"> * </span><span class="element_help" title="一个有效的电子邮件地址。帐号激活后的初始密码和所有本站发出的信件都将寄至此地址。电子邮件地址将不会被公开，仅当您想要接收新密码或通知时才会使用"> ? </span>
      </div>
      <div class="element_input">
         <input size="22" name="email" type="email" required="required">
      </div>
      
      <div class="element_label">
         <label>确认电子邮箱</label><span class="element_required"> * </span><span class="element_help" title="一个有效的电子邮件地址。帐号激活后的初始密码和所有本站发出的信件都将寄至此地址。电子邮件地址将不会被公开，仅当您想要接收新密码或通知时才会使用"> ? </span>
      </div>
      <div class="element_input">
         <input size="22" name="email2" type="email" required="required">
      </div>
   </fieldset>

   <fieldset>
      <legend>图形验证CAPTCHA</legend>
      <img id="captchaImage" title="图形验证" alt="图形验证未能正确显示，请刷新" src="<?php print $captcha; ?>">
      <a onclick="document.getElementById('captchaImage').setAttribute('src', '<?php print $captcha; ?>/' + Math.random().toString().slice(2));
         event.preventDefault();" href="#">看不清，换一张</a>
      <div class="form_element">
         <div class="element_label">
            <label>上面图片的内容是什么？</label><span class="element_required"> * </span><span class="element_help" title="Enter the characters shown in the image"> ? </span>
         </div>
         <div class="element_input">
            <input size="22" name="captcha" type="text" required="required">
         </div>
      </div>
   </fieldset>
   <fieldset>
      <legend>网站使用规范和免责声明</legend><a href="/node/23200">网站使用规范</a><br>
      <a href="/term">免责声明</a>
   </fieldset>
   <div class="element_input">
      <button type="submit">同意使用规范和免责声明，并创建新帐号</button>
   </div>
</form>