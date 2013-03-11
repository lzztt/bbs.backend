<div id="content">
  <div id="content-header">

    <h1 class="title"><?php echo $user->username; ?></h1>
    <div class="tabs">
       <ul class="tabs primary clear-block">
          <li class="active"><a class="active" href="/user/<?php echo $user->uid; ?>"><span class="tab">查看</span></a></li>
        <?php if ($uid == 1 || $uid == $user->uid): ?>
          <li><a href="/user/<?php echo $user->uid; ?>/edit"><span class="tab">编辑</span></a></li>
          <li><a href="/pm"><span class="tab">站内短信</span></a></li>
        <?php endif; ?>
        <li><a href="/user/<?php echo $user->uid; ?>/track"><span class="tab">跟踪</span></a></li>
      </ul>
    </div>
  </div> <!-- /#content-header -->

  <div id="content-area">
    <div class="profile">
      <div class="picture">
        <a class="active" title="浏览用户信息" href="/user/<?php echo $user->uid; ?>"><img title="<?php echo $user->username; ?> 的头像" alt="<?php echo $user->username; ?> 的头像" src="<?php echo ($user->avatar ? $user->avatar : '/data/avatars/avatar0' . mt_rand(1, 5) . '.jpg'); ?>"></a></div>
      <h3>财富</h3>

      <dl>
        <dt>金币</dt>
        <dd><?php echo $user->points; ?></dd>
      </dl>
      <h3>历史</h3>

      <dl class="user-member">
        <dt>注册于</dt>
        <dd><?php echo ($user->createTime); ?></dd>
      </dl>

      <dl class="user-member">
        <dt>最后在线时间</dt>
        <dd><?php echo ($user->lastAccessTime); ?></dd>
      </dl>

        <h3>性别</h3>

        <dl>
          <dt></dt>
          <dd><?php echo (is_null($user->sex) ? '未知' : (($user->sex == 1) ? '男' : '女')); ?></dd>
        </dl>

      <?php if ($uid != $user->uid): ?>
        <a class="button" href="/user/<?php echo $user->uid; ?>/pm">发送站内短信</a></div>
      <?php endif; ?>
  </div>
</div>