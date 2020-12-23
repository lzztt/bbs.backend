<?php
function (
  string $identCode
) {
?>

  您的安全验证码是： <?= $identCode ?>


  此安全验证码将在 10分钟 后过期，请尽快使用它完成您的操作。

<?php
};
