<?php if ($forum['cid'] != 2): ?><div class="breadcrumb"><a href="/forum">Forum</a></div><?php endif; ?>

<div id="forum">
   <ul>
      <?php foreach ($forum['children'] as $group): ?>
         <li>
            <a href="/forum/<?php echo $group['cid']; ?>"><?php echo $group['name']; ?></a>
            <?php echo $group['description']; ?>
         </li>
         <ul>
            <?php foreach ($group['children'] as $index => $board): ?>
               <li>
                  <a href="/forum/<?php echo $board['cid']; ?>"><?php echo $board['name']; ?></a>
                  <?php echo $board['description']; ?>
               </li>
            <?php endforeach; ?>
         </ul>
      <?php endforeach; ?>
   </ul>
</div>