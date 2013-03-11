<div class="author-pane">
   <div class="author-pane-inner">
      <div class="picture">
         <a title="浏览用户信息" href="/user/<?php echo $uid; ?>"><img title="<?php echo $username; ?> 的头像" alt="<?php echo $username; ?> 的头像" src="<?php echo $avatar; ?>" /><br /><?php echo $username; ?></a>
      </div>

      <div class="author-pane-line author-joined">
         <span class="author-pane-label">性别:</span> <?php echo $sex; ?>
      </div>
      <div class="author-pane-line author-joined">
         <span class="author-pane-label">加入:</span> <?php echo $joinTime; ?>
      </div>
      <div class="author-pane-line author-points">
         <span class="author-pane-label">金币</span>: <?php echo $points; ?>
      </div>

      <div class="author-pane-icon">
         <a href="/user/<?php echo $uid; ?>/pm"><img title="发送站内短信" alt="发送站内短信" src="/themes/default/images/pc/forum/pm.gif"></a>
      </div>
   </div>
</div>