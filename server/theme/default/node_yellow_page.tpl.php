<div id="content">
  <div id="content-area">
    <div class="node-type-yp" id="node-2622">
      <div class="node-inner">

        <div id="content-header"><?php print $breadcrumb; ?></div>

        <div class="forum-top-links">
          <ul class="links forum-links">
            <li data-urole='<?php print $urole_user; ?>'><a rel="nofollow" class="reply button" href="/node/<?php print $nid; ?>/comment">评论</a></li>
            <li data-urole='<?php print $urole_guest; ?>'>您需要先<a rel="nofollow" href="/user">登录</a>或<a href="/user/register">注册</a>才能发表评论</li>
          </ul>
        </div>

        <div class="reply-count" id="ajax_node_stat">
          <?php print $commentCount; ?> replies, <span id="ajax_viewCount_<?php print $nid; ?>"></span> views,
          <?php if (isset($pager)): ?>
            <div class="item-list">
              <ul class="pager"><?php print $pager; ?></ul>
            </div>
          <?php endif; ?>
        </div>
        <script type="text/javascript">
          $(document).ready(function() {
            $.getJSON('<?php print $ajaxURI; ?>', function(data) {
              var stat = $('#ajax_node_stat');
              for (var prop in data)
              {
                $('#ajax_' + prop, stat).html(data[prop]);
              }
            });
          });
        </script>
      </div>

      <div class="bcard">
        <table>
          <tbody>
            <tr><td>地址:</td><td><?php print $node['address']; ?></td></tr>
            <tr><td>电话:</td><td><?php print $node['phone']; ?></td></tr>
            <?php if (isset($node['fax'])): ?>
              <tr><td>传真:</td><td><?php print $node['fax']; ?></td></tr>
            <?php endif; ?>
            <?php if (isset($node['email'])): ?>
              <tr><td>电邮:</td><td><?php print $node['email']; ?></td></tr>
            <?php endif; ?>
            <?php if (isset($node['website'])): ?>
              <tr><td>网站:</td><td><a href="<?php print $node['website']; ?>" target="_blank"><?php print $node['website']; ?></a></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div style="padding: 8px 0;"><?php print $node['HTMLbody']; ?></div>

      <?php if ($node['attachments']): ?>
        <?php print $node['attachments']; ?>
      <?php endif; ?>

      <div>
        <?php for ($i = 1; $i <= 10; $i++): ?>
          <input name="star-avg" type="radio" class="star {split:2}" disabled="disabled" <?php print (((int) round($node['ratingAvg'] * 2)) == $i) ? 'checked="checked"' : ''; ?>/>
        <?php endfor; ?>
        <span> (<?php print '平均分：' . round($node['ratingAvg'], 1) . '，' . $node['ratingCount'] . '人评过分'; ?>)</span>
      </div>

    </div>

    <?php if (isset($comments)): ?>
      <div class="comments-node-type-yp" id="comments">
        <h2 id="comments-title">评论</h2>
        <?php foreach ($comments as $c): ?>
          <a id="comment<?php print $c['id']; ?>"></a>
          <div class="comment comment-published odd first">

            <div class="submitted">
              由 <a title="浏览用户信息" href="/user/<?php print $c['uid']; ?>"><?php print $c['username']; ?></a> 发表于 <?php print ($c['createTime']); ?><?php print ($c['lastModifiedTime']) ? '，最后修改于' . ($c['lastModifiedTime']) : ''; ?>。  </div>

            <div>
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <input name ="star_<?php print $c['id']; ?>" type="radio" class="star" disabled="disabled" <?php print ($c['rating'] == $i) ? 'checked="checked"' : ''; ?>/>
              <?php endfor; ?>
            </div>

            <div class="content" style="clear:both;"><?php print $c['HTMLbody']; ?></div>

            <div class="post-links user">
              <ul class="links inline forum-links">
                <li><a rel="nofollow" title="Edit" class="edit button" data-urole="<?php print $urole_adm . $tid . ' ' . $urole_user . $c['uid']; ?>" id="<?php print $c['type'] . '-' . $c['id']; ?>-edit" href="<?php print '/' . $c['type'] . '/' . $c['id'] . '/edit'; ?>">编辑</a></li>
                <li><a rel="nofollow" title="Delete" class="delete button" data-urole="<?php print $urole_adm . $tid . ' ' . $urole_user . $c['uid']; ?>" id="<?php print $c['type'] . '-' . $c['id']; ?>-delete" href="<?php print '/' . $c['type'] . '/' . $c['id'] . '/delete'; ?>">删除</a></li>
              </ul>
            </div>
            <div id="<?php print $c['type'] . '-' . $c['id']; ?>-raw" style="display:none;">
              <pre><?php print $c['body']; ?></pre>
            </div>
          </div>
        <?php endforeach; ?>
      </div> <!-- /comment-inner, /comment -->
    <?php endif; ?>

    <div class="box">
      <div class="box-inner" data-urole='<?php print $urole_user; ?>'>
        <h2 class="title">发表评论</h2>
        <div id="editor-div">
          <form id="editor-form" method="post" accept-charset="UTF-8" action="/node/comment/<?php print $nid; ?>">
            <div>
              <div id="edit-fivestar-rating-wrapper" class="form-item">
                <label for="edit-fivestar-rating">星级评分: <span style="font-size: 0.929em; font-weight: normal;">(每位用户只有最新的一次星级评分在系统中有效，如果您不想更新星级评分，可以忽略此选项。)</span></label>

                <input name="star" type="radio" class="star" value="1" title="非常差"/>
                <input name="star" type="radio" class="star" value="2" title="差"/>
                <input name="star" type="radio" class="star" value="3" title="一般"/>
                <input name="star" type="radio" class="star" value="4" title="好"/>
                <input name="star" type="radio" class="star" value="5" title="非常好"/>
                <span style="margin: 0pt 0pt 0pt 20px;" id="star_tip"></span>
              </div>
              <div id="edit-comment-wrapper" class="form-item">
                <label for="edit-comment">评论:<span title="此项必填。" class="form-required">*</span></label>
                <div class="resizable-textarea">
                  <textarea class="form-textarea required" id="TextEditor" name="body" rows="15" cols="60"></textarea>
                </div>
              </div>

              <input type="submit" class="form-submit" value="发布" id="edit-submit" />

            </div>
          </form>
        </div>
      </div>
      <div class="box-inner" data-urole='<?php print $urole_guest; ?>'>
        您需要先<a rel="nofollow" href="/user">登录</a>或<a rel="nofollow" href="/user/register">注册</a>才能发表评论
      </div>
    </div> <!-- /box-inner, /box -->
  </div>
</div>