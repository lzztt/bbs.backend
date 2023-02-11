<?php

use lzx\html\Template;
use site\City;

function (
  int $city,
  Template $hotForumTopicsWeekly,
  Template $hotForumTopicsMonthly,
  Template $imageSlider,
  Template $latestForumTopicReplies,
  Template $latestForumTopics,
) {
?>

  <div style="display:flex; flex-flow:row wrap">
    <div class="image_slider"><?= $imageSlider ?></div>
    <?php if ($city === City::HOUSTON) : ?>
      <div id="support_sm" class="home_items_sm"></div>
      <section class="home_items home_items_sm_down_bottom">
        <header>本月热门</header><?= $hotForumTopicsMonthly ?>
      </section>
    <?php elseif ($city === City::DALLAS) : ?>
      <div id="support_sm" class="home_items_sm"></div>
      <div class="home_items_md">
        <ins class="adsbygoogle home_items_md" data-ad-client="ca-pub-8257334386742604" data-ad-slot="4245946485" data-full-width-responsive="true"></ins>
        <script>
          (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
      </div>
    <?php elseif ($city === City::SFBAY) : ?>
      <div class="home_items_sm"><a href="/node/131734"><img src="/data/ad/new_green_922.jpg"></a></div>
      <div class="home_items_md"><a href="/node/131734"><img src="/data/ad/lotus_spring_922.jpg"></a></div>
    <?php endif ?>
    <section class="home_items">
      <header>最新话题</header><?= $latestForumTopics ?>
    </section>
    <section class="home_items">
      <header>最新论坛回复</header><?= $latestForumTopicReplies ?>
    </section>
    <?php if ($city !== City::SFBAY) : ?>
      <div id="support_xs"></div>
    <?php endif ?>
    <section class="home_items">
      <?php if ($city === City::HOUSTON) : ?>
        <header>本周热门</header><?= $hotForumTopicsWeekly ?>
      <?php else : ?>
        <header>本月热门</header><?= $hotForumTopicsMonthly ?>
      <?php endif ?>
    </section>
    <?php if ($city === City::HOUSTON) : ?>
      <div class="ad_xs">
        <ins class="adsbygoogle ad_xs" data-ad-client="ca-pub-8257334386742604" data-ad-slot="4245946485" data-full-width-responsive="true"></ins>
        <script>
          (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
      </div>
    <?php endif ?>
  </div>
  <div id='site_stat' class='ajax_load' data-ajax='/api/stat'>
    <div>当前在线用户：<span class='ajax_onlineCount'></span> (<span class='ajax_onlineUserCount'></span> 用户| <span class='ajax_onlineGuestCount'></span> 访客)</div>
    <div><span class='ajax_onlineUsers'></span></div>
    <div>统计</div>
    <div>
      <section>
        <span class='ajax_nodeCount'></span> 主题，<span class='ajax_postCount'></span> 贴子，<span class='ajax_userCount'></span> 用户，欢迎新进会员 <span class='ajax_latestUser'></span>
        <br>
        今日新主题 <span class='ajax_nodeTodayCount'></span> 个，今日新评论 <span class='ajax_commentTodayCount'></span> 个，今日新用户 <span class='ajax_userTodayCount'></span> 个
      </section>
    </div>
  </div>

<?php
};
