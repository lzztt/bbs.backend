<div>
   <div><h2>近期活动</h2><ul><?php echo $recentActivities; ?></ul></div>
   <div><h2>最新话题</h2><ul><?php echo $latestForumTopics; ?></ul></div>
   <div><h2>最新论坛回复</h2><ul><?php echo $latestForumTopicReplies; ?></ul></div>
   <div><h2>本周热门</h2><ul><?php echo $hotForumTopics; ?></ul></div>
   <div><h2>最新黄页</h2><ul><?php echo $latestYellowPages; ?></ul></div>
   <div><h2>最新黄页回复</h2><ul><?php echo $latestYellowPageReplies; ?></ul></div>
   <div><h2>签证移民信息</h2><ul><?php echo $latestImmigrationPosts; ?></ul></div>

   <div id="ajax_statistics">
      <div class="forum-statistics-sub-header" id="forum-statistics-active-header">
         当前在线用户：<span id='ajax_onlineCount'></span> (<span id='ajax_onlineUserCount'></span> 用户| <span id='ajax_onlineGuestCount'></span> 访客)
      </div>
      <div class="forum-statistics-sub-body" id="forum-statistics-active-body"><span id='ajax_onlineUsers'></span></div>

      <div class="forum-statistics-sub-header" id="forum-statistics-statistics-header">统计</div>
      <div class="forum-statistics-sub-body" id="forum-statistics-statistics-body">
         <ul style="list-style-type:none; margin:0; padding:0; overflow: hidden;">
            <li style="display:block;">
               <span id='ajax_nodeCount'></span> 主题，<span id='ajax_postCount'></span> 贴子，<span id='ajax_userCount'></span> 用户，欢迎新进会员 <span id='ajax_latestUser'></span>
               <br />
               今日新主题 <span id='ajax_nodeTodayCount'></span> 个，今日新评论 <span id='ajax_commentTodayCount'></span> 个，今日新用户 <span id='ajax_userTodayCount'></span> 个
            </li>
         </ul>
      </div>
   </div>
   <script type="text/javascript">
      $(document).ready(function() {
         $.getJSON('/home/ajax/stat?type=json', function(data){
            var stat = $('#ajax_statistics');
            for (var prop in data)
            {
               $('#ajax_' + prop, stat).html(data[prop]);
            }
         });
      });
   </script>
</div>

