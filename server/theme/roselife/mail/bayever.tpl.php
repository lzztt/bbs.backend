<!DOCTYPE html>
<html lang="zh" dir="ltr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
  <body>
    <article>
      Hi <?php print $username; ?>，<br>
      <br>
      感谢您<?php print $time; ?>对缤纷<?php print $city; ?>网的支持和关注。<br><br>
      我新上线了<a href="https://www.bayever.com" style="text-decoration:none;">bayever.com 生活在湾区</a>，<br>
      请过去留下您的脚印吧（老用户彩蛋：您现在的账号可以直接登录新网站）！<br>
      也欢迎把新网站介绍给您在加州湾区的华人朋友们！<br>
      感谢您多年来一直的支持！<br>
      <br>
      站长<br>
      龙璋
    </article>
    <br><br><a href="<?= $unsubscribeLink ?>" style="font-size:12px;color:#666666;text-decoration:none;">退订邮件 (Unsubscribe)</a><br>
  </body>
</html>
