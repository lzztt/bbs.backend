<?php

use lzx\html\Template;
use site\City;

function (
  int $city,
  Template $content,
  string $head_description,
  string $head_title,
  string $min_version,
  Template $page_navbar,
  string $sitename,
  bool $debug,
  string $theme
) {
?>

  <!DOCTYPE html>
  <html lang='zh' dir='ltr'>

  <head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <!--BEGIN JS-->
    <script defer src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous"></script>
    <script defer src="//cdnjs.cloudflare.com/ajax/libs/pica/6.1.1/pica.min.js" integrity="sha512-bfVc1C16JO+zN0PADKfNz2gZz+x3H1ZGo7aLsHz1i7XncAb3eE/GV559ndX1pMhwPtjwIe+0y9EgX99l4PmFIA==" crossorigin="anonymous"></script>

    <?php if ($debug) : ?>
      <script defer src="/themes/<?= $theme ?>/js/jquery.cookie.js"></script>
      <script defer src="/themes/<?= $theme ?>/js/jquery.imageslider.js"></script>
      <script defer src="/themes/<?= $theme ?>/js/jquery.hoverIntent.js"></script>
      <script defer src="/themes/<?= $theme ?>/js/jquery.superfish.js"></script>
      <script defer src="/themes/<?= $theme ?>/js/jquery.markitup.js"></script>
      <script defer src="/themes/<?= $theme ?>/js/jquery.markitup.bbcode.set.js"></script>
      <script defer src="/themes/<?= $theme ?>/js/image-blob-reduce.js"></script>
      <script defer src="/themes/<?= $theme ?>/js/main.js"></script>
    <?php else : ?>
      <script defer src="/themes/<?= $theme ?>/min/<?= $min_version ?>.min.js"></script>
    <?php endif ?>
    <!--END JS-->

    <!--BEGIN CSS-->
    <?php if ($debug) : ?>
      <link href="/themes/<?= $theme ?>/css/normalize.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $theme ?>/css/markitup.style.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $theme ?>/css/markitup.bbcode.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $theme ?>/css/nav_xs.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $theme ?>/css/nav_sm.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $theme ?>/css/main_xs.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $theme ?>/css/main_sm.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $theme ?>/css/main_md.css" rel="stylesheet" type="text/css">
      <link href="/themes/<?= $theme ?>/css/main_lg.css" rel="stylesheet" type="text/css">
      <?php if ($city === City::DALLAS || $city === City::SFBAY) : ?>
        <link href="/themes/<?= $theme ?>/css/main.dallas.css" rel="stylesheet" type="text/css">
      <?php endif ?>
      <link href="/themes/<?= $theme ?>/css/fontello.css" rel="stylesheet" type="text/css">
    <?php else : ?>
      <link href="/themes/<?= $theme ?>/min/<?= $min_version ?>.min.css" rel="stylesheet" type="text/css">
      <?php if ($city === City::DALLAS || $city === City::SFBAY) : ?>
        <link href="/themes/<?= $theme ?>/min/<?= $min_version ?>.dallas.min.css" rel="stylesheet" type="text/css">
      <?php endif ?>
    <?php endif ?>
    <!--END CSS-->

    <title><?= $head_title ?></title>
    <meta name='description' content='<?= $head_description ?>'>
    <?php if ($city === City::HOUSTON || $city === City::DALLAS) : ?>
      <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
      <script>
        (adsbygoogle = window.adsbygoogle || []).push({
          google_ad_client: "ca-pub-8257334386742604",
          enable_page_level_ads: true
        });
      </script>
    <?php endif ?>
    <style>
      .avatar_circle {
        width: 128px;
        height: 128px;
        line-height: 128px;
        border-radius: 50%;
        overflow: hidden;
        background: gray;
        margin-bottom: 0.3rem;
      }
      .avatar_circle div {
        margin: 0 1rem;
        white-space: nowrap;
        overflow: hidden;
        font-size: 50px;
        color: #fff;
        text-align: center;
      }
    </style>
  </head>

  <body>
    <div id='page'>
      <header id='page_header'>
        <div class="nav_mobile">
          <a class="icon-home" href="/">首页</a><a class="icon-left-big" href="#">后退</a><a class="icon-right-big" href="#">前进</a><a class="icon-cw" href="#">刷新</a><a class="icon-menu" href="#">菜单</a>
        </div>
        <?php if ($city === City::SFBAY) : ?>
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
        <?php endif ?>
        <div id="logo_div">
          <?php if ($city === City::HOUSTON || $city === City::DALLAS) : ?>
            <a id='logo' href='/'><img src='/themes/roselife/images/logo.png'></a>
            <span><?= $sitename ?></span>
          <?php elseif ($city === City::SFBAY) : ?>
            <span>bayever</span><span>forever</span>
          <?php endif ?>
        </div>
        <?php if ($city === City::HOUSTON) : ?>
          <div id="page_header_ad" style="background-image: url('/data/ad/ad_bg.jpg');">
            <span>
              <a href="http://31realty.net" target="_blank"><img src="/data/ad/31realty.jpg"></a>
              <a><img src="/data/ad/sunflower3.jpg"></a>
            </span>
          </div>
        <?php elseif ($city === City::DALLAS) : ?>
          <div id="page_header_ad" style="background-image: url('/data/ad/ad_bg.jpg');"><span><a></a></span></div>
        <?php elseif ($city === City::SFBAY) : ?>
          <div id="page_header_ad" style="background-image: url('/data/ad/bg_bayever.jpg');"><span><a></a></span></div>
        <?php endif ?>
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
        <?php if ($city === City::HOUSTON) : ?>
          <div id='copyright'>© 2009-2020 HoustonBBS 版权所有 | <a href='/term'>免责声明</a> | <a href="mailto:support@houstonbbs.com">联系我们</a> | <a href="mailto:ad@houstonbbs.com?subject=想在HoustonBBS上做个广告">广告洽谈</a></div>
        <?php elseif ($city === City::DALLAS) : ?>
          <div id='copyright'>© 2014-2020 DallasBBS 版权所有 | <a href='/term'>免责声明</a> | <a href="mailto:support@dallasbbs.com">联系我们</a></div>
        <?php elseif ($city === City::SFBAY) : ?>
          <div id='copyright'>© 2018-2020 bayever 版权所有 | <a href='/term'>免责声明</a> | <a href="mailto:support@bayever.com">联系我们</a></div>
        <?php endif ?>
      </footer>
    </div>
    <div id="messagebox"></div>
    <div id="popupbox"></div>
    <a id="goTop" class="button">返回顶部</a>
  </body>
  <?php if (!$debug) : ?>
    <?php
    if ($city === City::HOUSTON) :
      $track_id = 'UA-36671672-1';
    elseif ($city === City::DALLAS) :
      $track_id = 'UA-36671672-2';
    elseif ($city === City::SFBAY) :
      $track_id = 'UA-36671672-5';
    endif
    ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $track_id ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];

      function gtag() {
        dataLayer.push(arguments);
      }
      gtag('js', new Date());

      gtag('config', '<?= $track_id ?>');
    </script>
  <?php endif ?>

  </html>

<?php
};
