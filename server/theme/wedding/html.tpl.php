<!DOCTYPE html>
<html lang='zh' dir='ltr'>
   <head>
      <meta charset='UTF-8' />
      <meta name='viewport' content='width=device-width, initial-scale=1' />

      <?php include $tpl_path . '/head_js.tpl.php'; ?>
      <?php include $tpl_path . '/head_css.tpl.php'; ?>

      <title>Alex & Mika Wedding</title>

   </head>
   <body>

      <div id="wrapper">
         <ul class="nav">
            <li class="current"><img onclick="change_bg(this.src);"src="/themes/wedding/images/bg0.jpg" alt="" height="112px" width="168px" /></li>
            <li class=""><img onclick="change_bg(this.src);" src="/themes/wedding/images/bg1.jpg" alt="" height="112px" width="168px" /></li>
            <li class=""><img onclick="change_bg(this.src);"src="/themes/wedding/images/bg2.jpg" alt="" height="112px" width="168px" /></li>
            <li class=""><img onclick="change_bg(this.src);"src="/themes/wedding/images/bg3.jpg" alt="" height="112px" width="168px" /></li>
            <li class=""><img onclick="change_bg(this.src);"src="/themes/wedding/images/bg6.jpg" alt="" height="112px" width="168px" /></li>
         </ul>
         <div id="content">
            <?php echo $body; ?>
         </div>
      </div>
   </div>
</body>
</html>
