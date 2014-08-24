<div id="home_images"><div class="image_slider"><?php print $imageSlider; ?></div><div class="google_ad">
      <style>
         .responsive-ad { display:inline-block; width:300px; height:250px; }
         @media(max-width: 767px) { .responsive-ad { display:none } }
      </style>
      <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
      <!-- responsive_ad -->
      <ins class="adsbygoogle responsive-ad"
           data-ad-client="ca-pub-8257334386742604"
           data-ad-slot="1050744881"></ins>
      <script>
         (adsbygoogle = window.adsbygoogle || []).push({});
      </script>
   </div></div><section class="items home_activities">
   <header>近期活动</header><?php print $recentActivities; ?></section><section class="items"> 
   <header>最新话题</header><?php print $latestForumTopics; ?></section><section class="items">
   <header>最新论坛回复</header><?php print $latestForumTopicReplies; ?></section><section class="items home_hot_nodes">
   <header>本周热门</header><?php print $hotForumTopics; ?></section><section class="items">
   <header>最新黄页</header><?php print $latestYellowPages; ?></section><section class="items">
   <header>最新黄页回复</header><?php print $latestYellowPageReplies; ?></section><section class="items">
   <header>签证移民信息</header><?php print $latestImmigrationPosts; ?></section>
<div id='site_stat' class='ajax_load' data-ajax='/home/ajax/stat?type=json&nosession'>
   <div>当前在线用户：<span class='ajax_onlineCount'></span> (<span class='ajax_onlineUserCount'></span> 用户| <span class='ajax_onlineGuestCount'></span> 访客)</div>
   <div><span class='ajax_onlineUsers'></span></div>
   <div>统计</div>
   <div>
      <section>
         <span class='ajax_nodeCount'></span> 主题，<span class='ajax_postCount'></span> 贴子，<span class='ajax_userCount'></span> 用户，欢迎新进会员 <span class='ajax_latestUser'></span>
         <br>
         今日新主题 <span class='ajax_nodeTodayCount'></span> 个，今日新评论 <span class='ajax_commentTodayCount'></span> 个，今日新用户 <span class='ajax_userTodayCount'></span> 个
      </section><section><span class='ajax_alexa'></span></section>
   </div>
</div>
