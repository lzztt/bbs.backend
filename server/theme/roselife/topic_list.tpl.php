<div id="content">
   <div id="content-header">
      <?php print $breadcrumb; ?>
   </div> <!-- /#content-header -->

   <div id="content-area">
      <div id="forum">
         <div class="forum-description"><?php print $boardDescription; ?></div>
         <div class="forum-top-links">
            <ul class="links forum-links">
               <li data-urole='<?php print $urole_user; ?>'><a rel="nofollow" class="bb-create-node button" href="/forum/<?php print $tid; ?>/node">发表新话题</a></li>
               <li data-urole='<?php print $urole_guest; ?>'>您需要先<a rel="nofollow" href="/user">登录</a>或<a href="/user/register">注册</a>才能发表新话题</li>
            </ul>
         </div>

         <?php if ( isset( $pager ) ): ?>
            <div class="item-list"><ul class="pager"><?php print $pager; ?></ul></div>
         <?php endif; ?>
         <?php if ( isset( $nodes ) ): ?>
            <table class="forum-topics">
               <thead>
                  <tr>
                     <th>主题</th>
                     <th>回复/浏览</th>
                     <th>作者</th>
                     <th>最后回复</th>
                  </tr>
               </thead>

               <tbody class='even_odd_parent ajax_load' data-ajax='<?php print $ajaxURI; ?>'>
                  <?php foreach ( $nodes as $node ): ?>
                     <tr class="<?php print ($node[ 'weight' ] >= 2) ? 'topic-sticky' : ''; ?>">
                        <td><a href="/node/<?php print $node[ 'id' ]; ?>"><?php print $node[ 'title' ]; ?></a></td>
                        <td><?php print $node[ 'comment_count' ]; ?> / <span class="ajax_viewCount_<?php print $node[ 'id' ]; ?>"></span></td>     
                        <td><?php print $node[ 'creater_name' ]; ?>@<?php print ($node[ 'create_time' ] ); ?></td>
                        <td><?php if ( $node[ 'comment_count' ] > 0 ): ?><?php print $node[ 'commenter_name' ]; ?>@<?php print ($node[ 'comment_time' ] ); ?><?php endif; ?></td>
                     </tr>
                  <?php endforeach; ?>
               </tbody>
            </table>
         <?php endif; ?>

         <?php print $editor; ?>

         <?php if ( isset( $pager ) ): ?>
            <div class="item-list"><ul class="pager"><?php print $pager; ?></ul></div>
            <?php endif; ?>
      </div>
   </div>

</div>