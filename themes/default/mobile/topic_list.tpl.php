<div class="breadcrumb"><?php echo $breadcrumb; ?></div>
<h1 class="title"><?php echo $boardName; ?></h1>

<div class="forum-description"><?php echo $boardDescription; ?></div>

<?php if (isset($pager)): ?>
   <div class="item-list"><ul class="pager"><?php echo $pager; ?></ul></div>
<?php endif; ?>

<ul>
   <?php foreach ($nodes as $index => $node): ?>
      <li><?php echo ($node['weight'] >= 1) ? '置顶: ' : '' ?><a href="/node/<?php echo $node['nid']; ?>"><?php echo $node['title']; ?></a></li>
   <?php endforeach; ?>
</ul>


<?php if (isset($pager)): ?>
   <div class="item-list"><ul class="pager"><?php echo $pager; ?></ul></div>
<?php endif; ?>

<?php if ($uid > 0): ?>
   <form action="/forum/<?php echo $cid; ?>/node" accept-charset="UTF-8" method="post" id="editor-form" enctype="multipart/form-data">
      发表新话题：<br />
      <label for="title">标题：</label><br />
      <input type="text" placeholder="最少5个字母或3个汉字" maxlength="50" name="title" id="title" style='width: 90%;' required="required" value="" /><br />
      正文：
      <div>
         <textarea rows="10" style='width: 90%;' name="body" id="BBCodeEditor" class="text-full form-textarea" required="required" placeholder="最少5个字母或3个汉字"></textarea>
      </div>
      <input type="submit" id="edit-submit" value="Submit" class="form-submit" />
   </form>
<?php endif; ?>