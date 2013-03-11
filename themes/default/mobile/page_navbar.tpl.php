<a href="/">首页</a>
<?php if ($uid == 0): ?>
   <a href="/user/login">登录</a> <a href="/user/register">注册</a> <a href="/user/password">重设密码</a>
<?php else: ?>
   <a id="pm" href="/user/pm">短信</a> <a href="/user">我的账户</a> <a href="/user/logout">退出登录</a>
<?php endif; ?>
<a class="view_switch" href="#pc">电脑版</a>