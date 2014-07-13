<div id="content-area">
   <table id="commentTable">
      <tbody>
         <?php foreach ($comments as $i => $c): ?>
            <?php $n = $i + 1; ?>
            <tr  class="<?php print ($n % 2 == 0) ? 'even' : 'odd'; ?>">
               <td class="commentTableAuthor"><?php print '<b>' . $c['name'] . '</b><br />' . (date('m/d/Y H:i', $c['time'])); ?></td>
               <td class="commentTableFloor"><?php print $n; ?></td>
               <td><?php print nl2br($c['body']); ?></td>
            </tr>
         <?php endforeach; ?>
         <tr><td style="background-color: #CCCCCC; padding: 3px 6px;" colspan="3"><b>三十看从前活动留言</b><td></tr>
         <?php foreach ($comments_thirty as $i => $c): ?>
            <?php $n = $i + 1; ?>
            <tr  class="<?php print ($n % 2 == 0) ? 'even' : 'odd'; ?>">
               <td style="background-color: #CCCCCC;" class="commentTableAuthor"><?php print '<b>' . $c['name'] . '</b><br />' . (date('m/d/Y H:i', $c['time'])); ?></td>
               <td class="commentTableFloor"><?php print $n; ?></td>
               <td style="background-color: #CCCCCC;"><?php print nl2br($c['body']); ?></td>
            </tr>
         <?php endforeach; ?>
         <tr><td style="background-color: #CCCCCC; padding: 3px 6px;" colspan="3"><b>有钱人活动留言</b><td></tr>
         <?php foreach ($comments_rich as $i => $c): ?>
            <?php $n = $i + 1; ?>
            <tr  class="<?php print ($n % 2 == 0) ? 'even' : 'odd'; ?>">
               <td style="background-color: #CCCCCC;" class="commentTableAuthor"><?php print '<b>' . $c['name'] . '</b><br />' . (date('m/d/Y H:i', $c['time'])); ?></td>
               <td class="commentTableFloor"><?php print $n; ?></td>
               <td style="background-color: #CCCCCC;"><?php print nl2br($c['body']); ?></td>
            </tr>
         <?php endforeach; ?>
         <tr><td style="background-color: #CCCCCC; padding: 3px 6px;" colspan="3"><b>得闲饮茶活动留言</b><td></tr>
         <?php foreach ($comments_tea as $i => $c): ?>
            <?php $n = $i + 1; ?>
            <tr  class="<?php print ($n % 2 == 0) ? 'even' : 'odd'; ?>">
               <td style="background-color: #CCCCCC;" class="commentTableAuthor"><?php print '<b>' . $c['name'] . '</b><br />' . (date('m/d/Y H:i', $c['time'])); ?></td>
               <td class="commentTableFloor"><?php print $n; ?></td>
               <td style="background-color: #CCCCCC;"><?php print nl2br($c['body']); ?></td>
            </tr>
         <?php endforeach; ?>
         <tr><td style="background-color: #CCCCCC; padding: 3px 6px;" colspan="3"><b>七夕活动留言</b><td></tr>
         <?php foreach ($comments_qixi as $i => $c): ?>
            <?php $n = $i + 1; ?>
            <tr  class="<?php print ($n % 2 == 0) ? 'even' : 'odd'; ?>">
               <td style="background-color: #CCCCCC;" class="commentTableAuthor"><?php print '<b>' . $c['name'] . '</b><br />' . (date('m/d/Y H:i', $c['time'])); ?></td>
               <td class="commentTableFloor"><?php print $n; ?></td>
               <td style="background-color: #CCCCCC;"><?php print nl2br($c['body']); ?></td>
            </tr>
         <?php endforeach; ?>
      </tbody>
   </table>
</div>
