<table>
   <tbody>
      <tr>
         <th>姓名</th>
         <th>电子邮箱</th>
         <th>电话</th>
         <th>人数</th>
         <th>时间</th>
         <th>签到</th>
      </tr>
      <?php foreach ( $attendees as $i => $a ): ?>
         <tr>
            <td><?php echo $a['name']; ?></td>
            <td><?php echo $a['email']; ?></td>     
            <td><?php echo $a['phone']; ?></td>
            <td><?php echo $a['guests']; ?></td>
            <td><?php echo \date( 'm/d/Y', $a['time'] ); ?></td>
            <td><?php echo $a['checkin'] ? \date( 'm/d/Y H:m:s', $a['checkin']) : ''; ?></td>
         </tr>
      <?php endforeach; ?>
         <tr><td colspan="6">总人数：<?php print $total; ?></td></tr>
   </tbody>
</table>