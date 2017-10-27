<table>
  <tbody>
    <tr>
      <th>姓名</th>
      <th>新婚礼品</th>
      <th>礼品价值</th>
      <th>人数</th>
      <th>人数备注</th>
    </tr>
    <?php foreach ($tables as $i => $guests): ?>
      <?php foreach ($guests as $g): ?>
        <tr>
          <td><?= $g['name'] ?></td>
          <td><?= $g['gift'] ?></td>
          <td><?= $g['value'] ?></td>
          <td><?= $g['guests'] ?></td>
          <td><?= $g['comment'] ?></td>
        </tr>
      <?php endforeach ?>
        <tr style="background-color: gold;"><td colspan="10">金额：<?= $counts[$i] ?></td></tr>
    <?php endforeach ?>
    <tr style="background-color: #A7C942;"><td colspan="10">总金额：<?= $total ?></td></tr>
  </tbody>
</table>