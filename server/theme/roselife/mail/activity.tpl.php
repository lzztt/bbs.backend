<?php
function (
    string $domain,
    int $nid,
    string $sitename,
    string $title,
    string $username
) {
?>

    <?= $username ?> 您好，

    感谢您热心组织活动！您在 <?= $sitename ?> 网站的活动已被激活。激活后的活动将在首页和活动页面显示。
    [TITLE] <?= $title ?>
    [URL] https://<?= $domain ?>/node/<?= $nid ?>

<?php
};
