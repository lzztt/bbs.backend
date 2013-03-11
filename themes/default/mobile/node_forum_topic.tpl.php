<div id="content">

   <div id="content-header">
      <div class="breadcrumb"><?php echo $breadcrumb; ?></div>
      <h1 id="node-title" class="title"><?php echo $title; ?></h1>
      <div style="height: 5px;"></div>
   </div> <!-- /#content-header -->

   <div id="content-area">

      <div class="forum-topic-header clear-block">
         <a id="top"></a>

         <div class="forum-top-links">
            <ul class="links forum-links">
               <?php if ($isLoggedIn): ?>
                  <li><a class="bb-create-node button" href="/forum/<?php echo $tid; ?>/node">发表新话题</a></li>
                  <li><a class="bb-reply button" href="/node/<?php echo $nid; ?>/comment">回复</a></li>
               <?php else: ?>
                  <li>您需要先<a href="/user">登录</a>或<a href="/user/register">注册</a>才能发表话题或回复</li>
               <?php endif; ?>
            </ul>
         </div>
         <div class="reply-count" id="ajax_node_stat">
            <?php echo $commentCount; ?> replies, <span id="ajax_viewCount_<?php echo $nid; ?>"></span> views,
            <?php if (isset($pager)): ?>
               <div class="item-list">
                  <ul class="pager"><?php echo $pager; ?></ul>
               </div>
            <?php endif; ?>
         </div>
         <script type="text/javascript">
            $(document).ready(function() {
               $.getJSON('<?php echo $ajaxURI; ?>', function(data){
                  var stat = $('#ajax_node_stat');
                  for (var prop in data)
                  {
                     $('#ajax_' + prop, stat).html(data[prop]);
                  }
               });
            });
         </script>
      </div>

      <?php foreach ($posts as $index => $p): ?>
         <a id="<?php echo $p['type'] . $p['id']; ?>"></a>
         <div class="forum-post-wrapper">
            <div class="forum-post-panel-sub">

            </div>

            <div class="forum-post-panel-main clear-block" style="margin-bottom: <?php echo 10 + (empty($p['signature']) ? 0 : 63) + ($isLoggedIn ? 37 : 0); ?>px;">
               <div class="post-info clear-block">
                  <span class="posted-on">
                     <?php
                     echo $p['authorPanel'] . ' posted on ' . $p['createTime'];
                     if ($p['lastModifiedTime'])
                     {
                        echo ' (last modified at ' . $p['lastModifiedTime'] . ')';
                     }
                     ?>
                  </span>
                  <?php if ($p['type'] == 'comment'): ?>
                     <span class="post-num">#<?php echo $postNumStart + $index ?></span>
                  <?php endif; ?>
               </div>
               <div class="content">
                  <?php echo $p['HTMLbody']; ?>
                  <?php if ($p['attachments']): ?>
                     <div>本文附件:
                        <?php echo $p['attachments']; ?>
                     </div>
                  <?php endif; ?>
               </div>
            </div>
            <div class="forum-post-footer clear-block">
               <?php if ($p['signature']): ?>
                  <div class="author-signature">
                     <?php echo $p['signature']; ?>
                  </div>
               <?php endif; ?>
               <?php if ($isLoggedIn): ?>
                  <div class="forum-post-links">
                     <ul class="links inline forum-links">
                        <?php if ($tid == 16 && $p['type'] == 'node'): ?>
                           <li><a title="发布为活动" class="activity-link <?php echo 'u' . $p['uid']; ?> hidden button" id="<?php echo $p['type'] . '-' . $p['id']; ?>-activity" href="<?php echo '/' . $p['type'] . '/' . $p['id'] . '/activity'; ?>">发布为活动</a></li>
                        <?php endif; ?>
                        <li><a title="Edit" class="bb-edit <?php echo 'u' . $p['uid']; ?> hidden button" id="<?php echo $p['type'] . '-' . $p['id']; ?>-edit" href="<?php echo '/' . $p['type'] . '/' . $p['id'] . '/edit'; ?>">编辑</a></li>
                        <li><a title="Delete" class="delete <?php echo 'u' . $p['uid']; ?> hidden button" id="<?php echo $p['type'] . '-' . $p['id']; ?>-delete" href="<?php echo '/' . $p['type'] . '/' . $p['id'] . '/delete'; ?>">删除</a></li>
                        <li><a title="Reply" class="bb-reply button" id="<?php echo $p['type'] . '-' . $p['id']; ?>-reply" href="/node/<?php echo $nid; ?>/comment">回复</a></li>
                        <li><a title="Quote" class="bb-quote last button" id="<?php echo $p['type'] . '-' . $p['id']; ?>-quote" href="/node/<?php echo $nid; ?>/comment">引用</a></li>
                     </ul>
                  </div>
                  <div id="<?php echo $p['type'] . '-' . $p['id']; ?>-raw" style="display:none;">
                     <span class='username'><?php echo $p['username']; ?></span>
                     <pre class="postbody"><?php echo $p['body']; ?></pre>
                     <span class="files"><?php echo $p['filesJSON']; ?></span>
                  </div>
               <?php endif; ?>
            </div>
            <div style="clear:both;"></div><!--this is force the wrapper div have the correct height-->
         </div>
      <?php endforeach; ?>

      <?php if (isset($pager)): ?>
         <div class="item-list">
            <ul class="pager"><?php echo $pager; ?></ul>
         </div>
      <?php endif; ?>

      <?php echo $editor; ?>

   </div>
</div>