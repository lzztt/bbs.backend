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
               <td><a href="/forum/<?php print $board_id; ?>"><?php print $tags[ $board_id ][ 'name' ]; ?></a></td>
               <td><?php if ( $nodeInfo[ $board_id ][ 'node' ] ): ?><a href="/node/<?php print $nodeInfo[ $board_id ][ 'node' ][ 'nid' ]; ?>"><?php print $nodeInfo[ $board_id ][ 'node' ][ 'title' ]; ?></a><br><?php print $nodeInfo[ $board_id ][ 'node' ][ 'username' ]; ?> <span class='time'><?php print $nodeInfo[ $board_id ][ 'node' ][ 'create_time' ]; ?></span><?php endif; ?></td>
               <td><?php if ( $nodeInfo[ $board_id ][ 'comment' ] ): ?><a href="/node/<?php print $nodeInfo[ $board_id ][ 'comment' ][ 'nid' ]; ?>?page=last#comment<?php print $nodeInfo[ $board_id ][ 'comment' ][ 'cid' ]; ?>"><?php print $nodeInfo[ $board_id ][ 'comment' ][ 'title' ]; ?></a><br><?php print $nodeInfo[ $board_id ][ 'comment' ][ 'username' ]; ?> <span class='time'><?php print $nodeInfo[ $board_id ][ 'comment' ][ 'create_time' ]; ?></span><?php endif; ?></td>
            </tr>
         <?php endforeach; ?>
      </tbody>
   </table> 
<?php endforeach; ?>
