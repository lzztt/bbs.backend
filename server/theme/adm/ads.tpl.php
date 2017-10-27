<div class="front-items">
  <table id="attendees">
    <tbody><tr>
        <th>商家</th>
        <th>广告类型</th>
        <th>过期日期</th>
        <th>联系邮箱</th>
      </tr>
      <?php foreach ($ads as $i => $a): ?>
        <tr <?= ($i % 2 == 0) ? '' : 'class="alt"' ?> >
          <td><?= $a['name'] ?></td>
          <td><?= $a['type_id'] == 1 ? '电子黄页' : '页顶广告' ?></td>
          <td><?= date('m/d/Y', $a['exp_time']) ?></td>
          <td><?= $a['email'] ?></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
  <br />
  <table id="attendees">
    <tbody><tr>
        <th>商家</th>
        <th>金额</th>
        <th>付款日期</th>
        <th>过期日期</th>
        <th>备注</th>
      </tr>
      <?php foreach ($payments as $i => $p): ?>
        <tr <?= ($i % 2 == 0) ? '' : 'class="alt"' ?> >
          <td><?= $p['name'] ?></td>
          <td><?= '$' . $p['amount'] ?></td>
          <td><?= date('m/d/Y', $p['pay_time']) ?></td>
          <td><?= date('m/d/Y', $p['exp_time']) ?></td>
          <td><?= $p['comment'] ?></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>

<style type="text/css">
  #attendees {
    border-collapse: collapse;
    font-family: "Trebuchet MS",Arial,Helvetica,sans-serif;
    width: 100%;
  }

  #attendees th {
    background-color: #A7C942;
    color: #FFFFFF;
    font-size: 1.4em;
    padding-bottom: 4px;
    padding-top: 5px;
    text-align: left;
  }

  #attendees td, #attendees th {
    border: 1px solid #98BF21;
    padding: 3px 7px 2px;
  }

  #attendees tr.alt td {
    background-color: #EAF2D3;
    color: #000000;
  }
</style>
