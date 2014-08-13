<header class="content_header">
   <?php print $breadcrumb; ?>
   <span class='v_guest'>您需要先<a rel="nofollow" href="/user">登录</a>或<a href="/user/register">注册</a>才能发表新话题或回复</span>
   <button class='v_user' data-action="/forum/<?php print $tid; ?>/node">发表新话题</button>
   <button class='v_user' data-action="/node/<?php print $nid; ?>/comment">回复</button>
   <span class="ajax_load" data-ajax='<?php print $ajaxURI; ?>'><?php print $commentCount; ?> replies, <span class="ajax_viewCount_<?php print $nid; ?>"></span> views</span> 
   <?php print $pager; ?>
</header>


<?php foreach ( $posts as $index => $p ): ?>
   <a id="<?php print $p[ 'type' ] . $p[ 'id' ]; ?>"></a>
   <?php print $p[ 'authorPanel' ]; ?>
   <article>      
      <header>
         <a href="/user/<?php print $p[ 'uid' ]; ?>"><?php print $p[ 'username' ]; ?></a> <span class='city'><?php print $p[ 'city' ]; ?></span> @ 
         <?php
         echo $p[ 'createTime' ];
         if ( $p[ 'lastModifiedTime' ] )
         {
            echo ' (修改于 ' . $p[ 'lastModifiedTime' ] . ')';
         }
         ?>
         <?php if ( $p[ 'type' ] == 'comment' ): ?>
            <span class="comment_num">#<?php print $postNumStart + $index ?></span>
         <?php endif; ?>
      </header>
      
      <div class="article_content">
         <?php print $p[ 'HTMLbody' ] . $p[ 'attachments' ]; ?>
      </div>

      <footer class='v_user'>
         <div class="actions">
            <?php $urole = 'v_adm' . $tid . ' v_user' . $p[ 'uid' ]; ?>
            <?php if ( $tid == 16 && $p[ 'type' ] == 'node' ): ?>
               <button class="activity <?php print $urole; ?>" data-action="/node/<?php print $p[ 'id' ]; ?>/activity">发布为活动</button>
            <?php endif; ?>
            <button class="edit <?php print $urole; ?>" data-action="<?php print '/' . $p[ 'type' ] . '/' . $p[ 'id' ] . '/edit'; ?>">编辑</button>
            <button class="delete <?php print $urole; ?>" data-action="<?php print '/' . $p[ 'type' ] . '/' . $p[ 'id' ] . '/delete'; ?>">删除</button>
            <button class="reply " data-action="/node/<?php print $nid; ?>/comment">回复</button>
            <button class="quote" data-action="/node/<?php print $nid; ?>/comment">引用</button>
         </div>
         <div id="<?php print $p[ 'type' ] . '-' . $p[ 'id' ]; ?>-raw" style="display:none;">
            <span class='username'><?php print $p[ 'username' ]; ?></span>
            <pre class="postbody"><?php print $p[ 'body' ]; ?></pre>
            <span class="files"><?php print $p[ 'filesJSON' ]; ?></span>
         </div>
      </footer>
   </article>
<?php endforeach; ?>

<?php print $pager; ?>
<?php print $editor; ?>