<?php print $userLinks; ?>
<div>
   <figure>
      <img class="avatar" src="<?php print $avatar; ?>" alt="<?php print $username; ?>的头像">
      <figcaption><?php print $username; ?></figcaption>
      <?php if ( $pm ): ?>
         <a href="#/pm/send" class="button popup" data-vars='{"uid":<?php print $uid; ?>,"username":"<?php print $username; ?>"}'>发送站内短信</a>
      <?php endif; ?>
   </figure>
   <dl>
      <?php foreach ( $info as $k => $v ): ?>
         <dt><?php print $k; ?></dt><dd><?php print $v; ?></dd>
      <?php endforeach; ?>
   </dl>
</div>
<table class='user_topics'>
   <caption>最近发表的论坛话题</caption>
   <thead><tr><th>论坛话题</th><th>发表时间</th></tr></thead>
   <tbody class="even_odd_parent">
      <?php foreach ( $topics as $t ): ?>
         <tr>
            <td><a href="/node/<?php print $t[ 'nid' ]; ?>"><?php print $t[ 'title' ]; ?></a></td><td><?php print \date( 'm/d/Y H:i', $t[ 'createTime' ] ); ?></td>
         </tr>
      <?php endforeach; ?>
   </tbody>
</table>
<table class='user_topics'>
   <caption>最近回复的论坛话题</caption>
   <thead><tr><th>论坛话题</th><th>发表时间</th></tr></thead>
   <tbody class="even_odd_parent">
      <?php foreach ( $comments as $c ): ?>
         <tr>
            <td><a href="/node/<?php print $c[ 'nid' ]; ?>"><?php print $c[ 'title' ]; ?></a></td><td><?php print \date( 'm/d/Y H:i', $c[ 'createTime' ] ); ?></td>
         </tr>
      <?php endforeach; ?>
   </tbody>
</table>