<div id="navbar">
   <a href="/lottery" class="navlink">规则</a>
   <a href="/lottery/prize" class="navlink">奖品</a>
   <a href="/lottery/try" class="navlink">试一下</a>
   <a href="/lottery/start" class="navlink">开始抽奖</a>
   <a href="/lottery/rank" class="navlink">排名</a>
</div>

<div>

   <h1>圣诞-春节 节日抽奖</h1>

   <div class="headerbox">
      <span class="highlight"><?php echo $username; ?></span> 的抽奖记录
   </div>

   <?php echo '综合分数 : ' . sprintf('%8.3f', $average) . '<br />奖券平均值 : ' . sprintf('%8.3f', $aPoints[sizeof($aPoints)]) . ' (抽奖次数 : ' . sizeof($results[sizeof($results)]) . ' 次)<br />'; ?>

   <br />抽奖记录 :
   <table>
      <tbody>
         <?php foreach ($results as $round => $roundResults): ?>
            <tr>
               <th>时间</th>
               <th>分数 <?php echo '(' . sprintf('%4.1f', $aPoints[$round]) . ')'; ?></th>
            </tr>
            <?php foreach ($roundResults as $r): ?>
               <tr>
                  <td><?php echo date('m/d/Y H:i:s', $r['time']); ?> </td>
                  <td><?php echo $r['points']; ?> </td>
               </tr>
            <?php endforeach; ?>
         <?php endforeach; ?>
      </tbody>
   </table>

</div>