<h1 class="title">近期活动</h1>

<div><a href="/help#activity">如何发布活动</a></div>

<?php if (isset($pager)): ?>
   <div class="item-list"><ul class="pager"><?php echo $pager; ?></ul></div>
<?php endif; ?>

<ul class='activity_list'><?php echo $data; ?></ul>