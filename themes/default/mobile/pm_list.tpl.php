<div id="content">

  <div class="breadcrumb"><?php echo $breadcrumb; ?></div>
  <div id="content-area">
    <div id="forum">
      <?php if (isset($pager)): ?>
        <div class="item-list"><ul class="pager"><?php echo $pager; ?></ul></div>
      <?php endif; ?>

      <table class="privatemsg-list sticky-enabled tableSelect-processed sticky-table">
        <thead class="tableHeader-processed">
          <tr>
            <th class="privatemsg-header-subject">标题</th>
            <th class="privatemsg-header-participants">联系人</th>
            <th class="privatemsg-header-lastupdated active">开始时间</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($msgs as $k => $m): ?>
            <tr  class="<?php echo ($index % 2 == 0) ? 'even' : 'odd'; ?>">
              <td class="privatemsg-list-subject"><a href="/pm/<?php echo $m['mid']; ?>"><?php echo $m['subject'] . ($m['isNew'] == 1 ? ' (<span style="color:red;">new</span>)' : ''); ?> </a></td>
              <td class="privatemsg-list-participants"><?php echo $m['fromName'] . ' -> ' . $m['toName']; ?></td>
              <td class="privatemsg-list-date active"><?php echo ($m['time']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="item-list"><ul class="pager"><?php echo $pager; ?></ul></div>
    </div>
  </div>

</div> <!-- /#content-inner, /#content -->
