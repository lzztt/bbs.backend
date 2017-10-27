<div id="content">
  <div class="front-items">
    <div class="item-list" style="width: 66%;"><?= $imageSlider ?></div>
    <div class="item-list"><h2 class="title">近期活动</h2><?= $recentActivities ?></div>
  </div>
  <div class="front-items">
    <div class="item-list"><h2 class="title">最新话题</h2><?= $latestForumTopics ?></div>
    <div class="item-list"><h2 class="title">最新论坛回复</h2><?= $latestForumTopicReplies ?></div>
    <div class="item-list"><h2 class="title">本周热门</h2><?= $hotForumTopics ?></div>
  </div>
  <div class="front-items">
    <div class="item-list"><h2 class="title">最新黄页</h2><?= $latestYellowPages ?></div>
    <div class="item-list"><h2 class="title">最新黄页回复</h2><?= $latestYellowPageReplies ?></div>
    <div class="item-list"><h2 class="title">签证移民信息</h2><?= $latestImmigrationPosts ?></div>
  </div>

  <div id="ajax_statistics">
    <div class="forum-statistics-sub-header" id="forum-statistics-active-header">
      当前在线用户：<span id='ajax_onlineCount'></span> (<span id='ajax_onlineUserCount'></span> 用户| <span id='ajax_onlineGuestCount'></span> 访客)
    </div>
    <div class="forum-statistics-sub-body" id="forum-statistics-active-body"><span id='ajax_onlineUsers'></span></div>

    <div class="forum-statistics-sub-header" id="forum-statistics-statistics-header">统计</div>
    <div class="forum-statistics-sub-body" id="forum-statistics-statistics-body">
      <ul style="list-style-type:none; margin:0; padding:0; overflow: hidden;">
        <li style="display:block; float:left; width:50%;">
          <span id='ajax_nodeCount'></span> 主题，<span id='ajax_postCount'></span> 贴子，<span id='ajax_userCount'></span> 用户，欢迎新进会员 <span id='ajax_latestUser'></span>
          <br />
          今日新主题 <span id='ajax_nodeTodayCount'></span> 个，今日新评论 <span id='ajax_commentTodayCount'></span> 个，今日新用户 <span id='ajax_userTodayCount'></span> 个
        </li>
        <li style="display:block; float:left; width:45%;"><span id='ajax_alexa'></span></li>
      </ul>
    </div>
  </div>
  <script type="text/javascript">
    $(document).ready(function() {
      $.getJSON('/home/ajax/stat?type=json&nosession', function(data) {
        var stat = $('#ajax_statistics');
        for (var prop in data)
        {
          $('#ajax_' + prop, stat).html(data[prop]);
        }
      });
    });
  </script>
</div>


