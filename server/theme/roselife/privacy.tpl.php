<?php
function (
  string $site_zh_cn,
  string $site_en_us
) {
?>

  <article>
    <h2><?= $site_zh_cn ?>网站收集的信息</h2>
    <p>1. <?= $site_zh_cn ?>(<?= $site_en_us ?>)使用Cookie技术来区分已登录用户和匿名访客。Cookie技术是支持网站用户的登陆登出功能的必要技术。</p>
    <p>2. 匿名访客只能浏览网站的公开发帖和评论。登陆用户可以发布公开帖子和发送站内私信。<?= $site_zh_cn ?>只存储登陆用户的最少信息，包括：登陆邮箱，密码（加密存储），用户名，登陆IP和浏览器（登陆安全记录），以及用户发布的公开帖子和站内私信。</p>
    <p>3. <?= $site_zh_cn ?>使用GeoIP技术把用户登陆IP转化成城市名称，显示在公开信息页面以帮助其他用户判断信息的真实性。</p>
    <p>4. 用户可以联系<?= $site_zh_cn ?>来删除自己的账户，以删除所有用户相关信息。</p>

    <h2>第三方服务收集的信息</h2>
    <p>1. <?= $site_zh_cn ?>使用<a href="https://analytics.google.com">Google Analytics</a>统计所有访客（包括登陆用户和匿名访客）的浏览数据。该浏览数据为汇总统计数据。<?= $site_zh_cn ?>不与Google Analytics共享用户信息。Google Analytics用Cookie和JavaScript技术在浏览器端统计网页浏览信息</p>
    <?php if ($site_en_us !== 'bayever.com') : ?>
      <p>2. <?= $site_zh_cn ?>使用<a href="https://www.google.com/adsense">Google AdSense</a>提供的广告服务，Google AdSense用Cookie和JavaScript技术收集访客（包括登陆用户和匿名访客）正在浏览的网页信息以在该页面上显示相关的广告。Google AdSense不与<?= $site_zh_cn ?>共享广告信息和推荐算法。</p>
    <?php endif; ?>
  </article>

<?php
};
