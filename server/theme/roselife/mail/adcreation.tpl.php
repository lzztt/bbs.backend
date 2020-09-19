<?php
function (
    string $name,
    string $sitename,
    string $url
) {
?>

    <?= $name ?> 您好，

    您在<?= $sitename ?>网站的电子黄页已经创建成功。
    <?= $url ?>


    您可以通过以上链接访问，也欢迎分享给您的客户让他们去留好评支持。
    如有问题或者广告内容需要修改，可以随时联系我们。

    Best,
    <?= $sitename ?> Ads

<?php
};
