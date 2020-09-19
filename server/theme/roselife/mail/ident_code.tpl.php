<?php
function (
    string $ident_code,
    string $sitename,
    string $username
) {
?>

    <?= $username ?> 您好，

    您在 <?= $sitename ?> 的操作需要使用安全验证码才能继续执行

    您的安全验证码是： <?= $ident_code ?>


    此安全验证码将在 十分钟 后过期，请尽快使用它完成您的操作

<?php
};
