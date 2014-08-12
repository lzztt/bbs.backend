<div class="image_slider"><?php print $imageSlider; ?></div><section class="items">
   <header>近期活动</header><?php print $recentActivities; ?></section><section class="items">
   <header>最新话题</header><?php print $latestForumTopics; ?></section><section class="items">
   <header>最新论坛回复</header><?php print $latestForumTopicReplies; ?></section><section class="items">
   <header>本周热门</header><?php print $hotForumTopics; ?></section><section class="items">
   <header>最新黄页</header><?php print $latestYellowPages; ?></section><section class="items">
   <header>最新黄页回复</header><?php print $latestYellowPageReplies; ?></section><section class="items">
   <header>签证移民信息</header><?php print $latestImmigrationPosts; ?></section>
<div class='ajax_load' data-ajax='/home/ajax/stat?type=json&nosession'>
   <div>当前在线用户：<span class='ajax_onlineCount'></span> (<span class='ajax_onlineUserCount'></span> 用户| <span class='ajax_onlineGuestCount'></span> 访客)</div>
   <div><span class='ajax_onlineUsers'></span></div>
   <div>统计</div>
   <div>
      <section>
         <span class='ajax_nodeCount'></span> 主题，<span class='ajax_postCount'></span> 贴子，<span class='ajax_userCount'></span> 用户，欢迎新进会员 <span class='ajax_latestUser'></span>
         <br>
         今日新主题 <span class='ajax_nodeTodayCount'></span> 个，今日新评论 <span class='ajax_commentTodayCount'></span> 个，今日新用户 <span class='ajax_userTodayCount'></span> 个
      </section>
      <section><span class='ajax_alexa'></span></section>
   </div>
</div>
