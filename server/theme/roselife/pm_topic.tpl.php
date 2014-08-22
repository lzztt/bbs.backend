<?php print $userLinks; ?>
<?php print $mailBoxLinks; ?>
<?php foreach ( $msgs as $m ): ?>
   <article>      
      <header>
         <a href="/user/<?php print $m[ 'uid' ]; ?>"><?php print $m[ 'username' ]; ?></a> <?php print $m[ 'time' ]; ?>
      </header>

      <div class="article_content"><?php print $m[ 'body' ]; ?>
         <a class="button" href="/pm/<?php print $topicID; ?>/delete/<?php print $m[ 'id' ]; ?>"><?php print ( $m[ 'id' ] == $topicID ? '删除短信' : '删除' ); ?></a>
      </div>
   </article>  
<?php endforeach; ?>

<form accept-charset="UTF-8" autocomplete="off" method="post" action="/pm/<?php print $topicID; ?>/reply" id="pm_reply">
   <fieldset>
      <label class="label">收信人</label><input readonly="readonly" name="to" type="text" value="<?php print $replyTo[ 'username' ]; ?>">
   </fieldset>
   <fieldset>
      <label class="label" data-help="最少5个字母或3个汉字">回复内容</label><textarea  name="body" required></textarea>
   </fieldset>
   <input name="fromUID" value="<?php print $fromUID; ?>" type="hidden">
   <input name="toUID" value="<?php print $replyTo[ 'id' ]; ?>" type="hidden">
   <fieldset><button type="submit">发送</button></fieldset>
</form>