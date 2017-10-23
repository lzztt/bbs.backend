<div class="image_slider"><?php print $imageSlider; ?></div><div class="google_ad">
    <style>
      .responsive-ad-1 { display:inline-block; width:300px; height:250px; }
      @media(max-width: 995px) { .responsive-ad-1 { display:none } }
    </style>
    <!-- responsive_ad -->
    <ins class="adsbygoogle responsive-ad-1"
        data-ad-client="ca-pub-8257334386742604"
        data-ad-slot="4245946485"></ins>
    <script>
      (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
  </div><div class="google_ad">
    <style>
      .responsive-ad-2 { display:inline-block; width:300px; height:250px; }
      @media(max-width: 995px) { .responsive-ad-2 { display:none } }
    </style>
    <!-- responsive_ad -->
    <ins class="adsbygoogle responsive-ad-2"
        data-ad-client="ca-pub-8257334386742604"
        data-ad-slot="7199412884"></ins>
    <script>
      (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
  </div><section class="items">
  <header>最新话题</header><?php print $latestForumTopics; ?></section><section class="items">
  <header>最新论坛回复</header><?php print $latestForumTopicReplies; ?></section><section class="items home_hot_nodes">
  <header>本周热门</header><?php print $hotForumTopics; ?></section>
<?php foreach ( $groups as $group_id => $tags ): ?>
  <table>
    <thead>
      <tr>
        <th><?php print $tags[$group_id]['description']; ?></th>
        <th>最新话题</th>
        <th>最新回复</th>
      </tr>
    </thead>
    <tbody class='even_odd_parent'>
      <?php foreach ( $tags[$group_id]['children'] as $board_id ): ?>
        <tr>
          <td><a href="/forum/<?php print $board_id; ?>"><?php print $tags[$board_id]['name']; ?></a></td>
          <td><?php if ( $nodeInfo[$board_id]['node'] ): ?><a href="/node/<?php print $nodeInfo[$board_id]['node']['nid']; ?>"><?php print $nodeInfo[$board_id]['node']['title']; ?></a><br><?php print $nodeInfo[$board_id]['node']['username']; ?> <span class='time'><?php print $nodeInfo[$board_id]['node']['create_time']; ?></span><?php endif; ?></td>
          <td><?php if ( $nodeInfo[$board_id]['comment'] ): ?><a href="/node/<?php print $nodeInfo[$board_id]['comment']['nid']; ?>?p=l#comment<?php print $nodeInfo[$board_id]['comment']['cid']; ?>"><?php print $nodeInfo[$board_id]['comment']['title']; ?></a><br><?php print $nodeInfo[$board_id]['comment']['username']; ?> <span class='time'><?php print $nodeInfo[$board_id]['comment']['create_time']; ?></span><?php endif; ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endforeach; ?>
<div id='site_stat' class='ajax_load' data-ajax='/api/stat'>
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
