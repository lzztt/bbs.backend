<div id="content">

    <div id="content-header"><?php print $breadcrumb; ?></div> <!-- /#content-header -->

    <div id="content-area">

        <div class="forum-topic-header clear-block">
            <a id="top"></a>

            <div class="forum-top-links">
                <ul class="links forum-links">
                    <li data-urole='<?php print $urole_user; ?>'><a rel="nofollow" class="bb-create-node button" href="/forum/<?php print $tid; ?>/node">发表新话题</a></li>
                    <li data-urole='<?php print $urole_user; ?>'><a rel="nofollow" class="bb-reply button" href="/node/<?php print $nid; ?>/comment">回复</a></li>
                    <li data-urole='<?php print $urole_guest; ?>'>您需要先<a rel="nofollow" href="/user">登录</a>或<a rel="nofollow" href="/user/register">注册</a>才能发表话题或回复</li>
                </ul>
            </div>
            <div class="reply-count" id="ajax_node_stat">
                <?php print $commentCount; ?> replies, <span id="ajax_viewCount_<?php print $nid; ?>"></span> views,
                <?php if ( isset( $pager ) ): ?>
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

        <?php foreach ( $posts as $index => $p ): ?>
            <a id="<?php print $p['type'] . $p['id']; ?>"></a>
            <div class="forum-post-wrapper clear-block">
                <div class="forum-post-panel" data-umode='<?php print $umode_pc; ?>'>
                    <?php print $p['authorPanel']; ?>
                </div>

                <div class="forum-post-main">
                    <div class="post-info">
                        <span data-umode='<?php print $umode_mobile; ?>'><a rel="nofollow" href="/user/<?php print $p['uid']; ?>" title="浏览用户信息"><?php print $p['username']; ?></a> @ <?php print $p['city']; ?> @</span> 
                        <span class="posted-on">
                            <?php
                            echo $p['createTime'];
                            if ( $p['lastModifiedTime'] )
                            {
                                echo ' (last modified at ' . $p['lastModifiedTime'] . ')';
                            }
                            ?>
                        </span>
                        <?php if ( $p['type'] == 'comment' ): ?>
                            <span class="post-num">#<?php print $postNumStart + $index ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="post-content">
                        <?php print $p['HTMLbody']; ?>
                        <?php if ( $p['attachments'] ): ?>
                            <div>本文附件:
                                <?php print $p['attachments']; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="post-footer">
                        <div class="post-links" data-urole='<?php print $urole_user; ?>'>
                            <ul class="links inline forum-links">
                                <?php if ( $tid == 16 && $p['type'] == 'node' ): ?>
                                    <li><a rel="nofollow" title="发布为活动" class="activity-link button" data-urole="<?php print $urole_adm . $tid . ' ' . $urole_user . $p['uid']; ?>" id="<?php print $p['type'] . '-' . $p['id']; ?>-activity" href="/node/<?php print $p['id']; ?>/activity">发布为活动</a></li>
                                <?php endif; ?>
                                <li><a rel="nofollow" title="Edit" class="bb-edit button" data-urole="<?php print $urole_adm . $tid . ' ' . $urole_user . $p['uid']; ?>" id="<?php print $p['type'] . '-' . $p['id']; ?>-edit" href="<?php print '/' . $p['type'] . '/' . $p['id'] . '/edit'; ?>">编辑</a></li>
                                <li><a rel="nofollow" title="Delete" class="delete button" data-urole="<?php print $urole_adm . $tid . ' ' . $urole_user . $p['uid']; ?>" id="<?php print $p['type'] . '-' . $p['id']; ?>-delete" href="<?php print '/' . $p['type'] . '/' . $p['id'] . '/delete'; ?>">删除</a></li>
                                <li><a rel="nofollow" title="Reply" class="bb-reply button" id="<?php print $p['type'] . '-' . $p['id']; ?>-reply" href="/node/<?php print $nid; ?>/comment">回复</a></li>
                                <li><a rel="nofollow" title="Quote" class="bb-quote last button" id="<?php print $p['type'] . '-' . $p['id']; ?>-quote" href="/node/<?php print $nid; ?>/comment">引用</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div id="<?php print $p['type'] . '-' . $p['id']; ?>-raw" style="display:none;">
                    <span class='username'><?php print $p['username']; ?></span>
                    <pre class="postbody"><?php print $p['body']; ?></pre>
                    <span class="files"><?php print $p['filesJSON']; ?></span>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ( isset( $pager ) ): ?>
            <div class="item-list">
                <ul class="pager"><?php print $pager; ?></ul>
            </div>
        <?php endif; ?>

        <?php print $editor; ?>

    </div>
</div>