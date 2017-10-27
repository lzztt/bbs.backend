<!DOCTYPE html>
<html lang='zh' dir='ltr'>
  <head>
    <meta charset='UTF-8' />
    <meta name='viewport' content='width=device-width, initial-scale=1' />

    <?php include $tpl_path . '/head_js.tpl.php' ?>
    <?php include $tpl_path . '/head_css.tpl.php' ?>

    <title>Alex & Mika Wedding</title>

  </head>
  <body>
    <div id="page">
      <div id="bg"></div>
      <div id="wrapper">
        <ul class="nav">
          <li><img src="/themes/wedding/images/bg0.jpg" alt="" height="112px" width="168px" /></li>
          <li><img src="/themes/wedding/images/bg1.jpg" alt="" height="112px" width="168px" /></li>
          <li><img src="/themes/wedding/images/bg2.jpg" alt="" height="112px" width="168px" /></li>
          <li><img src="/themes/wedding/images/bg3.jpg" alt="" height="112px" width="168px" /></li>
          <li><img src="/themes/wedding/images/bg4.jpg" alt="" height="112px" width="168px" /></li>
        </ul>
        <button id="bgswitch" type="button">停止图片轮播</button>
        <div id="content">
          <?= $body ?>
        </div>
      </div>
    </div>
  </body>
</html>
