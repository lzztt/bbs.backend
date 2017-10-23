<a id='yp_join' href="/yp/join">加入黄页</a>

<ul class='even_odd_parent' id='yp_list'>
  <?php foreach ( $yp[$tid]['children'] as $groupID ): ?>
    <li class="l1">
      <a title="<?php print $yp[$groupID]['description']; ?>" href="/yp/<?php print $yp[$groupID]['id']; ?>"><?php print $yp[$groupID]['name']; ?></a>
      <ul>
        <?php foreach ( $yp[$groupID]['children'] as $tagID ): ?>
          <li class="l2"><a title="<?php print $yp[$tagID]['description']; ?>" href="/yp/<?php print $yp[$tagID]['id']; ?>"><?php print $yp[$tagID]['name']; ?></a></li>
          <?php endforeach; ?>
      </ul>
    </li>
  <?php endforeach; ?>
</ul>
