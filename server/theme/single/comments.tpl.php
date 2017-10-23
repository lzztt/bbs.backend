<h3>留言评论:</h3>
<?php foreach ( $comments as $c ): ?>
  <div class="comment">
    <div class="comment-author"><?php print $c['name']; ?><div class='time'><?php print \date( 'm/d/Y H:i', $c['time'] ); ?></div></div>
    <div class="comment-body"><?php print $c['body']; ?></div>
  </div>
<?php endforeach; ?>
