<ul class="tabs">
   <li><a href="/user/login">登录</a></li>
   <li><a href="/user/register">创建新帐号</a></li>
   <li class="active"><a href="/password/reset">重设密码</a></li>
   <li><a href="/user/username">忘记用户名</a></li>
</ul>
<form accept-charset="UTF-8" autocomplete="off" method="post" action="/user/password" id="user-pass">
   <div class="form_element">
      <label data-help="输入您的用户名">用户名</label><input size="22" name="username" type="text" required="required" />
   </div>
   <div class="form_element">
      <label data-help="输入您注册时使用的电子邮箱地址">注册电子邮箱地址</label><input size="22" name="email" type="email" required="required" />
   </div>
   <div class="form_element">
      <button type="submit">发送重设密码链接</button>
   </div>
</form>