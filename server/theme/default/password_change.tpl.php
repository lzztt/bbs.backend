<ul class="tabs">
   <li><a href="/user/display">用户首页</a></li>
   <li><a href="/user/pm">站内短信</a></li>
   <li><a href="/user/edit">编辑个人资料</a></li>
   <li class="active"><a href="/password/change">更改密码</a></li>
</ul>
<form accept-charset="UTF-8" autocomplete="off" method="post" action="/password/change" id="user-pass">
   <div class="form_element">
      <div class="element_label"><label>请输入旧密码</label></div>
      <div class="element_input"><input size="22" name="password_old" type="password"></div>
   </div>
   <div class="form_element">
      <div class="element_label"><label>请输入新密码</label></div>
      <div class="element_input"><input size="22" name="password_new" type="password"></div>
   </div>
   <div class="form_element">
      <div class="element_label"><label>请重新输入新密码</label></div>
      <div class="element_input"><input size="22" name="password_new2" type="password"></div>
   </div>
   <div class="form_element">
      <div class="element_input"><button type="submit">更改密码</button></div>
   </div>
</form>