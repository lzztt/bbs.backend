<!DOCTYPE html>
<html lang='zh' dir='ltr'>
  <head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <!--BEGIN JS-->
    <script defer src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous"></script>
    <script defer src="//cdnjs.cloudflare.com/ajax/libs/pica/6.1.1/pica.min.js" integrity="sha512-bfVc1C16JO+zN0PADKfNz2gZz+x3H1ZGo7aLsHz1i7XncAb3eE/GV559ndX1pMhwPtjwIe+0y9EgX99l4PmFIA==" crossorigin="anonymous"></script>

    <?php if ($tpl_debug): ?>
      <script defer src="/themes/<?= $tpl_theme ?>/js/jquery.cookie.js"></script>
      <script defer src="/themes/<?= $tpl_theme ?>/js/jquery.imageslider.js"></script>
      <script defer src="/themes/<?= $tpl_theme ?>/js/jquery.hoverIntent.js"></script>
      <script defer src="/themes/<?= $tpl_theme ?>/js/jquery.superfish.js"></script>
      <script defer src="/themes/<?= $tpl_theme ?>/js/jquery.markitup.js"></script>
      <script defer src="/themes/<?= $tpl_theme ?>/js/jquery.markitup.bbcode.set.js"></script>
      <script defer src="/themes/<?= $tpl_theme ?>/js/jquery.upload-1.0.2.js"></script>
      <script defer src="/themes/<?= $tpl_theme ?>/js/image-blob-reduce.js"></script>
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
      <link href="/themes/<?= $tpl_theme ?>/min/<?= $min_version ?>.dallas.min.css" rel="stylesheet" type="text/css">
    <?php endif ?>
    <!--END CSS-->

    <title><?= $head_title ?></title>
    <meta name='description' content='<?= $head_description ?>'>
  </head>
  <body>
    <div id='page'>
      <header id='page_header'>
        <div class="nav_mobile">
          <a class="icon-home" href="/">首页</a><a class="icon-left-big" href="#">后退</a><a class="icon-right-big" href="#">前进</a><a class="icon-cw" href="#">刷新</a><a class="icon-menu" href="#">菜单</a>
        </div>
        <style scoped>
        div#logo_div {
          display: none;
        }
        @media (min-width: 768px) {
          div#logo_div {
            display: block;
            font-size: 1.2rem;
            padding: .25rem;
          }
          div#logo_div span {
            padding: .25rem;
            border: 1px solid #28a745;
          }
          div#logo_div span:first-child {
            background-color: #28a745;
            color: #fff;
            border-top-left-radius: .25rem;
            border-bottom-left-radius: .25rem;
          }
          div#logo_div span:last-child {
            background-color: #fff;
            color: #28a745;
            border-top-right-radius: .25rem;
            border-bottom-right-radius: .25rem;
          }
        }
        </style>
        <div id="logo_div">
          <span>bayever</span><span>forever</span>
        </div>
        <div id="page_header_ad" style="background-image: url('/data/ad/bg_bayever.jpg');"><span><a></a></span></div>
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
        <div id='copyright'>© 2018-2020 bayever 版权所有 | <a href='/term'>免责声明</a> | <a href="mailto:support@bayever.com">联系我们</a></div>
      </footer>
    </div>
    <div id="messagebox"></div>
    <div id="popupbox"></div>
    <a id="goTop" class="button">返回顶部</a>
  </body>
  <?php if (!$tpl_debug): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-36671672-5"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'UA-36671672-5');
    </script>
  <?php endif ?>
</html>
