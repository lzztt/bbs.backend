<div id="navbar">
  <div class="navbar_menu">

    <ul id="navbar-menu" class="sf-menu">
      <li><a title="首页" href="/">首页</a></li>
      <li><a title="站内搜索" href="/search">搜索</a></li>
      <li><a title="华人活动" href="/activity">活动</a></li>
      <li><a title="社区抽奖" href="/lottery">抽奖</a></li>
      <li><a title="单身交友" href="/single">单身交友</a></li>
      <li><a title="缤纷休斯顿论坛" href="/forum">论坛</a>
        <ul style="display: none;">
          <li><a title="论坛使用手册" href="/forum/help">论坛帮助</a></li>
          <?= $forumMenu ?>
        </ul>
      </li>
      <li><a title="" href="/yp">黄页</a>
        <ul style="display: none;">
          <li><a title="加入 缤纷休斯顿 电子黄页" href="/yp/join">加入黄页</a></li>
          <?= $ypMenu ?>
        </ul>
      </li>
    </ul>
  </div> <!-- /block-inner, /block -->

  <div id='navbar_user'>
    <form id="navbar-login-form_tmp" action="/user/login" accept-charset="UTF-8" method="post">
      <ul class="sf-menu" data-urole='<?= $urole_user ?>'>
        <li data-umode='<?= $umode_mobile ?>'><a href="/" title="首页">首页</a></li>
        <li><a id="pm" class="popup" href="/pm/mailbox" title="短信">短信</a></li>
        <li><a class="popup" href="/user" title="我的账户">我的账户</a></li>
        <li><a class="popup" href="/user/logout" title="登出">登出</a></li>
        <li style="border-right:0px;"><a class="view_switch" data-umode='<?= $umode_pc ?>' href="#mobile" title="切换到手机版">手机版</a><a class="view_switch" data-umode='<?= $umode_mobile ?>' href="#pc" title="切换到电脑版">电脑版</a></li>
      </ul>
      <ul class="sf-menu" data-urole='<?= $urole_guest ?>'>
        <li data-umode='<?= $umode_mobile ?>'><a href="/" title="首页">首页</a></li>
        <li data-umode='<?= $umode_mobile ?>'><a class="popup" href="/user/login" title="登录">登录</a></li>
        <li data-umode='<?= $umode_pc ?>'>
          <label for="username">用户名:</label>
          <input id="edit-name" class="form-text" type="text" required="required" placeholder="用户名" maxlength="50" name="username" size="15" value="">
          <label for="password">密码:</label>
          <input id="edit-pass" class="form-text" type="password" required="required" placeholder="密码" name="password" maxlength="60" size="15">
          <input type="submit" id="edit-submit" value="登录" class="form-submit">
        </li>
        <li><a class="popup" href="/password/forget" title="重设用户密码">忘记密码</a></li>
        <li><a class="popup" href="/user/register" title="新用户注册">注册帐号</a></li>
        <li style="border-right:0px;"><a class="view_switch" data-umode='<?= $umode_pc ?>' href="#mobile" title="切换到手机版">手机版</a><a class="view_switch" data-umode='<?= $umode_mobile ?>' href="#pc" title="切换到电脑版">电脑版</a></li>
      </ul>
    </form>
  </div>
</div>
