<?= $breadcrumb ?>
<?php foreach ($groups as $group_id => $tags): ?>
  <table>
    <thead>
      <tr>
        <th><?= $tags[$group_id]['description'] ?></th>
        <th>最新话题</th>
        <th>最新回复</th>
      </tr>
    </thead>
    <tbody class='even_odd_parent'>
      <?php foreach ($tags[$group_id]['children'] as $board_id): ?>
        <tr>
          <td><a href="/forum/<?= $board_id ?>"><?= $tags[$board_id]['name'] ?></a></td>
          <td><?php if ($nodeInfo[$board_id]['node']): ?><a href="/node/<?= $nodeInfo[$board_id]['node']['nid'] ?>"><?= $nodeInfo[$board_id]['node']['title'] ?></a><br><?= $nodeInfo[$board_id]['node']['username'] ?> <span class='time'><?= $nodeInfo[$board_id]['node']['create_time'] ?></span><?php endif ?></td>
          <td><?php if ($nodeInfo[$board_id]['comment']): ?><a href="/node/<?= $nodeInfo[$board_id]['comment']['nid'] ?>?p=l#comment<?= $nodeInfo[$board_id]['comment']['cid'] ?>"><?= $nodeInfo[$board_id]['comment']['title'] ?></a><br><?= $nodeInfo[$board_id]['comment']['username'] ?> <span class='time'><?= $nodeInfo[$board_id]['comment']['create_time'] ?></span><?php endif ?></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
<?php endforeach ?>
