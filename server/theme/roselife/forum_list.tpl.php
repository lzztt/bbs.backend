<?php print $breadcrumb; ?>
<?php foreach ( $groups as $group_id => $tags ): ?>
   <table>
      <thead>
         <tr> 
            <th><?php print $tags[ $group_id ][ 'description' ]; ?></th>
            <th>最新话题</th>
            <th>最新回复</th>
         </tr>
      </thead>
      <tbody class='even_odd_parent'>
         <?php foreach ( $tags[ $group_id ][ 'children' ] as $board_id ): ?>
            <tr>
               <td>
                  <a href="/forum/<?php print $board_id; ?>"><?php print $tags[ $board_id ][ 'name' ]; ?></a>
               </td>
               <td>
                  <?php if ( $nodeInfo[ $board_id ][ 'node' ] ): ?>
                     <a href="/node/<?php print $nodeInfo[ $board_id ][ 'node' ][ 'nid' ]; ?>"><?php print $nodeInfo[ $board_id ][ 'node' ][ 'title' ]; ?></a><br /><?php print $nodeInfo[ $board_id ][ 'node' ][ 'username' ]; ?>@<?php print $nodeInfo[ $board_id ][ 'node' ][ 'create_time' ]; ?>
                  <?php endif; ?>
               </td>
               <td>
                  <?php if ( $nodeInfo[ $board_id ][ 'comment' ] ): ?>
                     <a href="/node/<?php print $nodeInfo[ $board_id ][ 'comment' ][ 'nid' ]; ?>?page=last#comment<?php print $nodeInfo[ $board_id ][ 'comment' ][ 'cid' ]; ?>"><?php print $nodeInfo[ $board_id ][ 'comment' ][ 'title' ]; ?></a><br /><?php print $nodeInfo[ $board_id ][ 'comment' ][ 'username' ]; ?>@<?php print $nodeInfo[ $board_id ][ 'comment' ][ 'create_time' ]; ?>
                  <?php endif; ?>
               </td>
            </tr>
         <?php endforeach; ?>
      </tbody>
   </table> 
<?php endforeach; ?>

<div style="border:1px solid #DDDDDD; color:#666666; margin-top:15px; clear: both;">
   <div style="padding:0.4em 0.7em;"><a href="/node/210">Links (点击申请 友情链接)</a></div>
   <div style="padding:0.4em 0.7em; background:#EEEEEE none repeat scroll 0 0;">
      <a style="padding: 4px;" target="_blank" href="http://www.ricebbs.net">Rice BBS</a>
      <a style="padding: 4px;" target="_blank" href="http://www.hellogwu.com">GWUCSSA</a>
      <a style="padding: 4px;" target="_blank" href="http://www.utcssa.net">UT Austin CSSA</a>
      <a style="padding: 4px;" target="_blank" href="http://www.enteme.net">海外之缘</a>电脑技术
      <a style="padding: 4px;" target="_blank" href="http://www.uslifes.com">海外生活</a>
      <a style="padding: 4px;" target="_blank" href="http://www.xibian.net">西边摄影</a>
      <a style="padding: 4px;" target="_blank" href="http://www.hi8d.com">HI8D国际短信</a>
   </div>
</div>
