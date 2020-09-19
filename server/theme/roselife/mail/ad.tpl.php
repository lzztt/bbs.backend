<?php
function (
  array $ad
) {
?>

  <?= $ad['name'] ?> 您好，

  您在缤纷休斯顿网站的<?= $ad['type_id'] == 1 ? '电子黄页' : '页顶广告' ?>即将于<?= date('m/d/Y', (int) $ad['exp_time']) ?>到期。
  请问你是否要继续刊登？ 如果要续的话，可以由以下付款链接在线付款。谢谢！

  广告续登链接：
  http://www.houstonbbs.com/ad

  Best,
  HoustonBBS Ads

<?php
};
