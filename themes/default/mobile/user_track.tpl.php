<div id="content">

  <div class="breadcrumb"><?php echo $breadcrumb; ?></div>
  <div id="content-area">
    <table class="sticky-enabled sticky-table">
      <thead class="tableHeader-processed"><tr><th>论坛话题</th><th>发表时间</th> </tr></thead>
      <tbody>
        <?php foreach($posts as $k=>$p): ?>
        <tr class="<?php echo ($index % 2 == 0) ? 'even' : 'odd'; ?>"><td><a href="/node/<?php echo $p['nid']; ?>"><?php echo $p['title']; ?></a> </td><td><?php echo ($p['createTime']); ?></td> </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div> <!-- /#content-inner, /#content -->