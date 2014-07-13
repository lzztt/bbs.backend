<?php print $userLinks; ?>
<?php print $mailBoxLinks; ?>
<?php print $pager; ?>
<table>
   <thead>
      <tr><th>短信</th><th>联系人</th><th>时间</th></tr>
   </thead>
   <tbody class="js_even_odd_parent">
      <?php foreach ( $msgs as $m ): ?>
         <tr>
            <td><?php if ( $m[ 'is_new' ] ): ?><span style="color:red;">new</span><?php endif; ?><a href="/pm/<?php print $m[ 'msg_id' ]; ?>"><?php print $m[ 'body' ]; ?></a></td>
            <td><a href="/user/<?php print $m[ 'uid' ]; ?>"><?php print $m[ 'user' ]; ?></a></td>
            <td><?php print $m[ 'time' ]; ?></td>
         </tr>
      <?php endforeach; ?>
   </tbody>
</table>
<?php print $pager; ?>