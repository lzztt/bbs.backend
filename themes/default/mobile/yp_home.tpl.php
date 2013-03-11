<ul style="padding-left: 1.5em;">
   <?php foreach ($yp['children'] as $group): ?>
      <li>
         <a title="<?php echo $group['description']; ?>" href="/yp/<?php echo $group['tid']; ?>"><?php echo $group['name']; ?></a>
         <ul>
            <?php foreach ($group['children'] as $cate): ?>
               <li><a title="<?php echo $cate['description']; ?>" href="/yp/<?php echo $cate['tid']; ?>"><?php echo $cate['name']; ?></a></li>
            <?php endforeach; ?>
         </ul>
      </li>
   <?php endforeach; ?>
</ul>