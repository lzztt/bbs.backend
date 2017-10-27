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
    <?php foreach ($tables as $i => $guests): ?>
      <?php foreach ($guests as $g): ?>
        <tr>
          <td><?= $g['name'] ?></td>
          <td><?= $g['tid'] ?></td>
          <td><?= $g['guests'] ?></td>
          <td><?= $g['email'] ?></td>
          <td><?= $g['phone'] ?></td>
          <td><?= \date('m/d/Y', $g['time']) ?></td>
          <td><?= $g['checkin'] ? \date('m/d/Y H:m:s', $g['checkin']) : '' ?></td>
        </tr>
      <?php endforeach ?>
        <tr style="background-color: gold;"><td colspan="10">人数：<?= $counts[$i] ?></td></tr>
    <?php endforeach ?>
    <tr style="background-color: #A7C942;"><td colspan="10">总人数：<?= $total ?></td></tr>
  </tbody>
</table>