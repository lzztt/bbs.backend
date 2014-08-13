<ul class="sf-menu">
   <li><a href="/">首页</a></li>
   <li><a href="/search">搜索</a></li>
   <li><a href="/activity">活动</a></li>
   <li><a href="/single">单身交友</a></li>
   <li><a href="/forum">论坛</a>
      <ul style="display: none;">
         <li><a href="/forum/help">论坛帮助</a></li>
         <?php print $forumMenu; ?>
      </ul>
   </li>
   <li><a href="/yp">黄页</a>
      <ul style="display: none;">
         <li><a href="/yp/join">加入黄页</a></li>
         <?php print $ypMenu; ?>
      </ul>
   </li>
   <li class='v_user'><a href="/user">我的账户</a></li>
   <li class='v_user'><a href="/password/change">更改密码</a></li>
   <li class='v_guest'><a href="/user/login">登录</a></li>
   <li class='v_guest'><a href="/password/forget">忘记密码</a></li>
   <li class='v_guest'><a href="/user/username">忘记用户名</a></li>
   <li class='v_guest'><a href="/user/register">注册帐号</a></li>
</ul>
