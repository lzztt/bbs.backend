<div>

   <h1>圣诞-春节 节日抽奖</h1>

   <div class="headerbox">共有 <span class="highlight"><?php echo $userCount; ?></span> 位用户参与，抽奖 <span class="highlight"><?php echo $recordCount; ?></span> 次</div>

   <table>
      <thead>
         <tr>
            <th>排名</th>
            <th>用户</th>
            <th>综合分数</th>
            <th>抽奖记录</th>
            <th>海选</th>
            <th>十六进八</th>
            <th>八进四</th>
            <th>半决赛</th>
            <th>决赛</th>
         </tr>
      </thead>
      <tbody>
         <?php foreach ($rank as $k => $r): ?>
            <tr>
               <td><?php echo $k + 1; ?></td>
               <td><?php echo '<a href="/user/' . $r['uid'] . '">' . $r['username'] . '</a>'; ?></td>
               <td><?php echo sprintf('%8.3f', $r['points']); ?></td>
               <td><?php echo '<a href="/lottery/rank/record/' . $r['uid'] . '">查看</a>'; ?></td>
               <td><?php echo ($r['points1'] > 0.0001) ? sprintf('%8.3f', $r['points1']) : ''; ?></td>
               <td><?php echo ($r['points2'] > 0.0001) ? sprintf('%8.3f', $r['points2']) : ''; ?></td>
               <td><?php echo ($r['points3'] > 0.0001) ? sprintf('%8.3f', $r['points3']) : ''; ?></td>
               <td><?php echo ($r['points4'] > 0.0001) ? sprintf('%8.3f', $r['points4']) : ''; ?></td>
               <td><?php echo ($r['points5'] > 0.0001) ? sprintf('%8.3f', $r['points5']) : ''; ?></td>
            </tr>
         <?php endforeach; ?>
      </tbody>
   </table>

</div>