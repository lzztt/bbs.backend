<aside class="author_panel">
   <div class="picture">
      <a title="浏览用户信息" href="/user/<?php print $uid; ?>"><img width="120" height="120" title="<?php print $username; ?> 的头像" alt="<?php print $username; ?> 的头像" src="<?php print $avatar; ?>"><br><?php print $username; ?></a>
   </div>
   <div>
      <span>性别:</span> <?php print $sex; ?>
   </div>
   <div>
      <span>城市:</span> <?php print $city; ?>
   </div>
   <div>
      <span>加入:</span> <?php print $joinTime; ?>
   </div>
   <div>
      <span>金币</span>: <?php print $points; ?>
   </div>
   <div>
      <a href="/user/<?php print $uid; ?>/pm"><img title="发送站内短信" alt="发送站内短信" src="/themes/default/images/forum/pm.gif"></a>
   </div>
</aside>