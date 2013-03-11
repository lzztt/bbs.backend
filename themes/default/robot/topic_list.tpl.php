<div class="breadcrumb"><?php echo $breadcrumb; ?></div>
<h1 class="title"><?php echo $boardName; ?></h1>

<div class="forum-description"><?php echo $boardDescription; ?></div>

<?php if (isset($pager)): ?>
   <div class="item-list"><ul class="pager"><?php echo $pager; ?></ul></div>
<?php endif; ?>

<ul>
   <?php foreach ($nodes as $index => $node): ?>
      <li><a href="/node/<?php echo $node['nid']; ?>"><?php echo $node['title']; ?></a></li>
   <?php endforeach; ?>
</ul>


<?php if (isset($pager)): ?>
   <div class="item-list"><ul class="pager"><?php echo $pager; ?></ul></div>
<?php endif; ?>