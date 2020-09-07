<!DOCTYPE html>
<html lang='zh' dir='ltr'>
  <head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <!--BEGIN JS-->
    <!--[if lt IE 9]>
    <script defer src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script defer src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script>
      if ('querySelector' in document && 'localStorage' in window && 'addEventListener' in window) {
        document.write('<script defer src="//code.jquery.com/jquery-2.1.3.min.js"><\/script>');
      } else {
        document.write('<script defer src="//code.jquery.com/jquery-1.11.2.min.js"><\/script>');
      }
    </script>
    <script>(typeof JSON === 'object') || document.write('<script defer src="//cdnjs.cloudflare.com/ajax/libs/json3/3.3.2/json3.min.js"><\/script>')</script>

    <?php if ($tpl_debug): ?>
      <script defer src="/themes/<?= $tpl_theme ?>/js/jquery.cookie.js"></script>
      <script defer src="/themes/<?= $tpl_theme ?>/js/jquery.imageslider.js"></script>
      <script defer src="/themes/<?= $tpl_theme ?>/js/jquery.hoverIntent.js"></script>
      <script defer src="/themes/<?= $tpl_theme ?>/js/jquery.superfish.js"></script>
      <script defer src="/themes/<?= $tpl_theme ?>/js/jquery.markitup.js"></script>
      <script defer src="/themes/<?= $tpl_theme ?>/js/jquery.markitup.bbcode.set.js"></script>
      <script defer src="/themes/<?= $tpl_theme ?>/js/jquery.upload-1.0.2.js"></script>
      <script defer src="/themes/<?= $tpl_theme ?>/js/main.js"></script>
    <?php else: ?>
      <script defer src="/themes/<?= $tpl_theme ?>/min/<?= $min_version ?>.min.js"></script>
    <?php endif ?>
    <!--END JS-->

    <!--BEGIN CSS-->
    <?php if ($tpl_debug): ?>
      <link href="/themes/<?= $tpl_theme ?>/css/normalize.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $tpl_theme ?>/css/markitup.style.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $tpl_theme ?>/css/markitup.bbcode.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $tpl_theme ?>/css/nav_xs.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $tpl_theme ?>/css/nav_sm.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $tpl_theme ?>/css/main_xs.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $tpl_theme ?>/css/main_sm.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $tpl_theme ?>/css/main_md.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $tpl_theme ?>/css/main_lg.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $tpl_theme ?>/css/main.dallas.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $tpl_theme ?>/css/fontello.css" rel="stylesheet" type="text/css">
    <?php else: ?>
      <link href="/themes/<?= $tpl_theme ?>/min/<?= $min_version ?>.min.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $tpl_theme ?>/min/1464241922.dallas.min.css" rel="stylesheet" type="text/css">
    <?php endif ?>
    <!--END CSS-->

    <title><?= $head_title ?></title>
    <meta name='description' content='<?= $head_description ?>'>
    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <script>
      (adsbygoogle = window.adsbygoogle || []).push({
       google_ad_client: "ca-pub-8257334386742604",
       enable_page_level_ads: true
      });
    </script>
  </head>
  <body>
    <div id='page'>
      <header id='page_header'>
        <div class="nav_mobile">
          <a class="icon-home" href="/">首页</a><a class="icon-left-big" href="#">后退</a><a class="icon-right-big" href="#">前进</a><a class="icon-cw" href="#">刷新</a><a class="icon-menu" href="#">菜单</a>
        </div>
        <div id="logo_div">
          <a id='logo' href='/'><img src='/themes/roselife/images/logo.png'></a>
          <span><?= $sitename ?></span> <span class='slogan'>简爱生活 由此开始</span>
        </div>
        <div id="page_header_ad" style="background-image: url('/data/ad/ad_bg.jpg');"><span><a
              href="/node/68810" target="_blank"><img src="/data/ad/geekpush2.jpg"></a></span></div>
      </header>
      <nav id='page_navbar' class='hidden'>
        <ul class="sf-menu" style="display: inline-block; float: right;">
          <li class='v_user'><a id='username' href="/app/user">我的账户</a></li>
          <li class='v_user'><a id='pm' href="/app/user/mailbox/inbox">短信</a></li>
          <li class='v_user'><a href="/app/user/bookmark">收藏夹</a></li>
          <li class='v_user'><a class='popup' href="#changePassword">更改密码</a></li>
          <li class='v_user'><a href="/app/user/logout">登出</a></li>
          <li class='v_guest'><a class='popup' href="#login">登录</a></li>
          <li class='v_guest'><a href="/app/user/forget_password">忘记密码</a></li>
          <li class='v_guest'><a href="/app/user/forget_username">忘记用户名</a></li>
          <li class='v_guest'><a href="/app/user/register">注册帐号</a></li>
        </ul>
        <?= $page_navbar ?>
      </nav>
      <section id='page_body'><?= $content ?></section>
      <footer id='page_footer'>
        <div id='copyright'>© 2014-2020 DallasBBS 版权所有 | <a href='/term'>免责声明</a> | <a href="mailto:support@dallasbbs.com">联系我们</a></div>
      </footer>
    </div>
    <div id="messagebox"></div>
    <div id="popupbox"></div>
    <a id="goTop" class="button">返回顶部</a>
  </body>
  <?php if (!$tpl_debug): ?>
    <script>
      (function(i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function() {
          (i[r].q = i[r].q || []).push(arguments)
        }, i[r].l = 1 * new Date();
        a = s.createElement(o),
              m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
      })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

      ga('create', 'UA-36671672-2', 'auto');
      ga('send', 'pageview');
    </script>
  <?php endif ?>
</html>
