<table>
  <tbody>
    <tr>
      <th>姓名</th>
      <th>桌号</th>
      <th>人数</th>
      <th>电子邮箱</th>
      <th>电话</th>
      <th>时间</th>
      <th>签到</th>
    </tr>
    <?php foreach ( $tables as $i => $guests ): ?>
      <?php foreach ( $guests as $g ): ?>
        <tr>
          <td><?php echo $g['name']; ?></td>
          <td><?php echo $g['tid']; ?></td>
          <td><?php echo $g['guests']; ?></td>
          <td><?php echo $g['email']; ?></td>
          <td><?php echo $g['phone']; ?></td>
          <td><?php echo \date( 'm/d/Y', $g['time'] ); ?></td>
          <td><?php echo $g['checkin'] ? \date( 'm/d/Y H:m:s', $g['checkin'] ) : ''; ?></td>
        </tr>
      <?php endforeach; ?>
        <tr style="background-color: gold;"><td colspan="10">人数：<?php print $counts[$i]; ?></td></tr>
    <?php endforeach; ?>
    <tr style="background-color: #A7C942;"><td colspan="10">总人数：<?php print $total; ?></td></tr>
  </tbody>
</table>