<table>
   <tbody>
      <tr>
         <th>姓名</th>
         <th>新婚礼品</th>
         <th>礼品价值</th>
         <th>人数</th>
         <th>人数备注</th>
      </tr>
      <?php foreach ( $tables as $i => $guests ): ?>
         <?php foreach ( $guests as $g ): ?>
            <tr>
               <td><?php echo $g[ 'name' ]; ?></td>
               <td><?php echo $g[ 'gift' ]; ?></td>     
               <td><?php echo $g[ 'value' ]; ?></td>
               <td><?php echo $g[ 'guests' ]; ?></td>
               <td><?php echo $g[ 'comment' ]; ?></td>
            </tr>
         <?php endforeach; ?>
            <tr style="background-color: gold;"><td colspan="10">金额：<?php print $counts[ $i ]; ?></td></tr>
      <?php endforeach; ?>
      <tr style="background-color: #A7C942;"><td colspan="10">总金额：<?php print $total; ?></td></tr>
   </tbody>
</table>