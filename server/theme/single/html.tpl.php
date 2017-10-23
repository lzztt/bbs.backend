<!DOCTYPE html>
<html lang='zh' dir='ltr'>
  <head>
    <meta charset='UTF-8' />
    <meta name='viewport' content='width=device-width, initial-scale=1' />

    <?php include $tpl_path . '/head_js.tpl.php'; ?>
    <?php include $tpl_path . '/head_css.tpl.php'; ?>

    <title><?php print $title; ?></title>

  </head>
  <body>
    <div id="page">
      <div id="header">
        <div id="logo">
          <a href="/"><img id="logo-image" alt="首页" src="/themes/single/images/logo.png"></a>
        </div>
        <div id='slogan'>简单，把时间还给生活</div>
        <div id='navbar'><a href="/single">活动首页</a> <a href="/single/activities">往昔活动</a></div>
      </div>
      <div id="content"><?php print $content; ?></div>
      <div id="footer"></div>
    </div>
    <a id="goTop" class="button">返回顶部</a>
  </body>
</html>
