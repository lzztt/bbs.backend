<div id="content">

   <div id="content-header"><?= $breadcrumb ?></div> <!-- /#content-header -->

   <div id="content-area">

      <div class="forum-topic-header clear-block">
        <a id="top"></a>

        <div class="forum-top-links">
           <ul class="links forum-links">
              <li data-urole='<?= $urole_user ?>'><a rel="nofollow" class="bb-create-node button" href="/forum/<?= $tid ?>/node">发表新话题</a></li>
              <li data-urole='<?= $urole_user ?>'><a rel="nofollow" class="bb-reply button" href="/node/<?= $nid ?>/comment">回复</a></li>
              <li data-urole='<?= $urole_guest ?>'>您需要先<a rel="nofollow" href="/user">登录</a>或<a rel="nofollow" href="/user/register">注册</a>才能发表话题或回复</li>
           </ul>
        </div>
        <div class="reply-count" id="ajax_node_stat">
           <?= $commentCount ?> replies, <span id="ajax_viewCount_<?= $nid ?>"></span> views,
           <?php if (isset($pager)): ?>
              <div class="item-list">
                <ul class="pager"><?= $pager ?></ul>
              </div>
           <?php endif ?>
        </div>
        <script type="text/javascript">
           $(document).ready(function() {
              $.getJSON('<?= $ajaxURI ?>', function(data) {
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
        <a id="<?= $p['type'] . $p['id'] ?>"></a>
        <div class="forum-post-wrapper clear-block">
           <div class="forum-post-panel" data-umode='<?= $umode_pc ?>'>
              <?= $p['authorPanel'] ?>
           </div>

           <div class="forum-post-main">
              <div class="post-info">
                <span data-umode='<?= $umode_mobile ?>'><a rel="nofollow" href="/user/<?= $p['uid'] ?>" title="浏览用户信息"><?= $p['username'] ?></a> @ <?= $p['city'] ?> @</span>
                <span class="posted-on">
                   <?= $p['createTime'] . ($p['lastModifiedTime'] ? ' (last modified at ' . $p['lastModifiedTime'] . ')' : '') ?>
                </span>
                <?php if ($p['type'] == 'comment'): ?>
                   <span class="post-num">#<?= $postNumStart + $index ?></span>
                <?php endif ?>
              </div>
              <div class="post-content">
                <?= $p['HTMLbody'] ?>
                <?php if ($p['attachments']): ?>
                   <div>本文附件:
                      <?= $p['attachments'] ?>
                   </div>
                <?php endif ?>
              </div>

              <div class="post-footer">
                <div class="post-links" data-urole='<?= $urole_user ?>'>
                   <ul class="links inline forum-links">
                      <?php if ($tid == 16 && $p['type'] == 'node'): ?>
                        <li><a rel="nofollow" title="发布为活动" class="activity-link button" data-urole="<?= $urole_adm . $tid . ' ' . $urole_user . $p['uid'] ?>" id="<?= $p['type'] . '-' . $p['id'] ?>-activity" href="/node/<?= $p['id'] ?>/activity">发布为活动</a></li>
                      <?php endif ?>
                      <li><a rel="nofollow" title="Edit" class="bb-edit button" data-urole="<?= $urole_adm . $tid . ' ' . $urole_user . $p['uid'] ?>" id="<?= $p['type'] . '-' . $p['id'] ?>-edit" href="<?= '/' . $p['type'] . '/' . $p['id'] . '/edit' ?>">编辑</a></li>
                      <li><a rel="nofollow" title="Delete" class="delete button" data-urole="<?= $urole_adm . $tid . ' ' . $urole_user . $p['uid'] ?>" id="<?= $p['type'] . '-' . $p['id'] ?>-delete" href="<?= '/' . $p['type'] . '/' . $p['id'] . '/delete' ?>">删除</a></li>
                      <li><a rel="nofollow" title="Reply" class="bb-reply button" id="<?= $p['type'] . '-' . $p['id'] ?>-reply" href="/node/<?= $nid ?>/comment">回复</a></li>
                      <li><a rel="nofollow" title="Quote" class="bb-quote last button" id="<?= $p['type'] . '-' . $p['id'] ?>-quote" href="/node/<?= $nid ?>/comment">引用</a></li>
                   </ul>
                </div>
              </div>
           </div>
           <div id="<?= $p['type'] . '-' . $p['id'] ?>-raw" style="display:none;">
              <span class='username'><?= $p['username'] ?></span>
              <pre class="postbody"><?= $p['body'] ?></pre>
              <span class="files"><?= $p['filesJSON'] ?></span>
           </div>
        </div>
      <?php endforeach ?>

      <?php if (isset($pager)): ?>
        <div class="item-list">
           <ul class="pager"><?= $pager ?></ul>
        </div>
      <?php endif ?>

      <?= $editor ?>

   </div>
</div>
