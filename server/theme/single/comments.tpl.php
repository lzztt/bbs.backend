<h3>留言评论:</h3>
<?php foreach ($comments as $c): ?>
  <div class="comment">
    <div class="comment-author"><?= $c['name'] ?><div class='time'><?= date('m/d/Y H:i', $c['time']) ?></div></div>
    <div class="comment-body"><?= $c['body'] ?></div>
  </div>
<?php endforeach ?>
