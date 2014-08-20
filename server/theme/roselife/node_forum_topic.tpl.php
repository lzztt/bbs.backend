<header class="content_header">
   <?php print $breadcrumb; ?>
   <span class='v_guest'>您需要先<a rel="nofollow" href="/user">登录</a>或<a href="/user/register">注册</a>才能发表新话题或回复</span>
   <button type="button" class='v_user create_node' data-action="/forum/<?php print $tid; ?>/node">发表新话题</button>
   <button type="button" class='v_user reply' data-action="/node/<?php print $nid; ?>/comment">回复</button>
   <span class="ajax_load" data-ajax='<?php print $ajaxURI; ?>'><?php print $commentCount; ?> replies, <span class="ajax_viewCount_<?php print $nid; ?>"></span> views</span> 
   <?php print $pager; ?>
</header>


<?php foreach ( $posts as $index => $p ): ?>
   <a id="<?php print $p[ 'type' ] . $p[ 'id' ]; ?>"></a>
   <?php print $p[ 'authorPanel' ]; ?>
   <article>      
      <header>
         <a href="/user/<?php print $p[ 'uid' ]; ?>"><?php print $p[ 'username' ]; ?></a> <span class='city'><?php print $p[ 'city' ]; ?></span>
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
         <?php if ( $index == 0 ): ?>
            <style>
               .responsive-ad { display:inline-block; float:right; width:300px; height:250px; }
               @media(max-width: 767px) { .responsive-ad { display:none } }
            </style>
            <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
            <!-- responsive_ad -->
            <ins class="adsbygoogle responsive-ad"
                 data-ad-client="ca-pub-8257334386742604"
                 data-ad-slot="1050744881"></ins>
            <script>
               (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
         <?php endif; ?>
         <?php print $p[ 'HTMLbody' ] . $p[ 'attachments' ]; ?>
      </div>

      <footer class='v_user'>
         <div class="actions">
            <?php $urole = 'v_user_superadm v_user_tagadm_' . $tid . ' v_user' . $p[ 'uid' ]; ?>
            <?php if ( $tid == 16 && $p[ 'type' ] == 'node' ): ?>
               <button type="button" class="activity <?php print $urole; ?>" data-action="/node/<?php print $p[ 'id' ]; ?>/activity">发布为活动</button>
            <?php endif; ?>
            <button type="button" class="edit <?php print $urole; ?>" data-raw="#<?php print $p[ 'type' ] . '_' . $p[ 'id' ]; ?>_raw" data-action="<?php print '/' . $p[ 'type' ] . '/' . $p[ 'id' ] . '/edit'; ?>">编辑</button>
            <button type="button" class="delete <?php print $urole; ?>" data-action="<?php print '/' . $p[ 'type' ] . '/' . $p[ 'id' ] . '/delete'; ?>">删除</button>
            <button type="button" class="reply " data-action="/node/<?php print $nid; ?>/comment">回复</button>
            <button type="button" class="quote" data-raw="#<?php print $p[ 'type' ] . '_' . $p[ 'id' ]; ?>_raw" data-action="/node/<?php print $nid; ?>/comment">引用</button>
         </div>
         <div id="<?php print $p[ 'type' ] . '_' . $p[ 'id' ]; ?>_raw" style="display:none;">
            <pre class='username'><?php print $p[ 'username' ]; ?></pre>
            <pre class="body"><?php print $p[ 'body' ]; ?></pre>
            <pre class="files"><?php print $p[ 'filesJSON' ]; ?></pre>
         </div>
      </footer>
   </article>
<?php endforeach; ?>

<?php print $pager; ?>
<?php print $editor; ?>