<div id="navbar">
   <div class="navbar_menu">

      <ul id="navbar-menu" class="sf-menu">
         <li><a title="首页" href="/">首页</a></li>
         <li><a title="站内搜索" href="/search">搜索</a></li>
         <li><a title="华人活动" href="/activity">活动</a></li>
         <li><a title="社区抽奖" href="/lottery">抽奖</a></li>
         <li><a title="单身交友" href="/single">单身交友</a></li>
         <li><a class="sf-with-ul" title="缤纷休斯顿论坛" href="/forum">论坛</a>
            <ul style="visibility: hidden; display: none;">
               <li><a title="论坛使用手册" href="/forum/help">论坛帮助</a></li>
               <?php echo $forumMenu; ?>
            </ul>
         </li>
         <li><a class="sf-with-ul" title="" href="/yp">黄页</a>
            <ul style="visibility: hidden; display: none;">
               <li><a title="加入 缤纷休斯顿 电子黄页" href="/yp/join">加入黄页</a></li>
               <?php echo $ypMenu; ?>
            </ul>
         </li>
      </ul>
   </div> <!-- /block-inner, /block -->

   <div id='navbar_user'>
      <form id="navbar-login-form_tmp" action="/user/login" accept-charset="UTF-8" method="post">
         <ul class="sf-menu">
            <?php if ($uid > 0): ?>
               <li><a id="pm" class="popup" href="/user/pm" title="短信">短信</a></li>
               <li><a class="popup" href="/user" title="我的账户">我的账户</a></li>
               <li><a class="popup" href="/user/logout" title="登出">登出</a></li>
            <?php else: ?>
               <li>
                  <label for="username">用户名:</label>
                  <input id="edit-name" class="form-text" type="text" required="required" placeholder="用户名" maxlength="50" name="username" size="15" value="">
                  <label for="password">密码:</label>
                  <input id="edit-pass" class="form-text" type="password" required="required" placeholder="密码" name="password" maxlength="60" size="15">
                  <input type="submit" id="edit-submit" value="登录" class="form-submit">
               </li>
               <li><a class="popup" href="/user/password" title="重设用户密码">忘记密码</a></li>
               <li><a class="popup" href="/user/register" title="新用户注册">注册帐号</a></li>
            <?php endif; ?>
            <li style="border-right:0px;"><a class="view_switch" href="#mobile" title="切换到手机版">手机版</a></li>
         </ul>
      </form>
   </div>
</div>
