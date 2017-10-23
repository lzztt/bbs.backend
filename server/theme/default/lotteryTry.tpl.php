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
    这里可以无限测试 :)<br />每次抽奖得到的数值为0到100内的一个随机数字<br />抽奖次数越多，平均值将会越接近中间数字50 lol<br />
    <a  href="/node/10445">抽奖活动讨论贴</a>
  </div>

  <div style="margin: 10px 0;"><a class="bigbutton" href="/lottery/try/run">点击抽奖</a></div>


  <?php print '奖券平均值 : ' . sprintf('%8.3f', sizeof($lottery) > 0 ? (array_sum($lottery) / sizeof($lottery)) : 0) . ' (抽奖次数 : ' . @sizeof($lottery) . ' 次)<br />'; ?>

  <br />抽奖记录 (<a href="/lottery/try/clear">清空</a>) :
  <ul>
    <?php foreach ($lottery as $k => $v): ?>
      <li><?php print '[' . date('m/d/Y H:i:s', $k) . '] : ' . $v; ?> </li>
    <?php endforeach; ?>
  </ul>

</div>