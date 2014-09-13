<div class="image_slider"><?php print $imageSlider; ?></div><section class="items">
   <header>最新话题</header><?php print $latestForumTopics; ?></section><section class="items">
   <header>最新论坛回复</header><?php print $latestForumTopicReplies; ?></section><section class="items home_hot_nodes">
   <header>本周热门</header><?php print $hotForumTopics; ?></section>
<?php foreach ( $groups as $group_id => $tags ): ?>
   <table>
      <thead>
         <tr> 
            <th><?php print $tags[ $group_id ][ 'description' ]; ?></th>
            <th>最新话题</th>
            <th>最新回复</th>
         </tr>
      </thead>
      <tbody class='even_odd_parent'>
         <?php foreach ( $tags[ $group_id ][ 'children' ] as $board_id ): ?>
            <tr>
               <td><a href="/forum/<?php print $board_id; ?>"><?php print $tags[ $board_id ][ 'name' ]; ?></a></td>
               <td><?php if ( $nodeInfo[ $board_id ][ 'node' ] ): ?><a href="/node/<?php print $nodeInfo[ $board_id ][ 'node' ][ 'nid' ]; ?>"><?php print $nodeInfo[ $board_id ][ 'node' ][ 'title' ]; ?></a><br><?php print $nodeInfo[ $board_id ][ 'node' ][ 'username' ]; ?> <span class='time'><?php print $nodeInfo[ $board_id ][ 'node' ][ 'create_time' ]; ?></span><?php endif; ?></td>
               <td><?php if ( $nodeInfo[ $board_id ][ 'comment' ] ): ?><a href="/node/<?php print $nodeInfo[ $board_id ][ 'comment' ][ 'nid' ]; ?>?page=last#comment<?php print $nodeInfo[ $board_id ][ 'comment' ][ 'cid' ]; ?>"><?php print $nodeInfo[ $board_id ][ 'comment' ][ 'title' ]; ?></a><br><?php print $nodeInfo[ $board_id ][ 'comment' ][ 'username' ]; ?> <span class='time'><?php print $nodeInfo[ $board_id ][ 'comment' ][ 'create_time' ]; ?></span><?php endif; ?></td>
            </tr>
         <?php endforeach; ?>
      </tbody>
   </table> 
<?php endforeach; ?>
<div id='site_stat' class='ajax_load' data-ajax='/home/ajax/stat'>
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
<div style="border:1px solid #DDDDDD; margin-top:15px; clear: both;">
   <div style="padding:0.4em 0.7em;">友情链接</div>
   <div style="padding:0.4em 0.7em; background:#EEEEEE none repeat scroll 0 0;">
      <a style="padding: 4px;" target="_blank" href="http://www.houstonbbs.com">缤纷休斯顿</a>
      <a style="padding: 4px;" target="_blank" href="http://www.utswcssa.org">西南医学中心学生学者联谊会</a>
      <a style="padding: 4px;" target="_blank" href="http://www.dallas8.net">达拉斯吧</a>
   </div>
</div>
