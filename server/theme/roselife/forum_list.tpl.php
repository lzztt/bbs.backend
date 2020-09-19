<?php

use lzx\html\Template;
use site\City;

function (
  int $city,
  Template $breadcrumb,
  array $groups,
  array $nodeInfo
) {
?>

  <?= empty($breadcrumb) ? '' : $breadcrumb ?>
  <?php foreach ($groups as $group_id => $tags) : ?>
    <table>
      <thead>
        <tr>
          <th><?= $tags[$group_id]['description'] ?></th>
          <th>最新话题</th>
          <th>最新回复</th>
        </tr>
      </thead>
      <tbody class='even_odd_parent'>
        <?php foreach ($tags[$group_id]['children'] as $board_id) : ?>
          <tr>
            <td><a href="/forum/<?= $board_id ?>"><?= $tags[$board_id]['name'] ?></a></td>
            <td><?php if ($nodeInfo[$board_id]['node']) : ?><a href="/node/<?= $nodeInfo[$board_id]['node']['nid'] ?>"><?= $nodeInfo[$board_id]['node']['title'] ?></a><br><?= $nodeInfo[$board_id]['node']['username'] ?> <span class='time'><?= $nodeInfo[$board_id]['node']['create_time'] ?></span><?php endif ?></td>
            <td><?php if ($nodeInfo[$board_id]['comment']) : ?><a href="/node/<?= $nodeInfo[$board_id]['comment']['nid'] ?>?p=l#comment<?= $nodeInfo[$board_id]['comment']['cid'] ?>"><?= $nodeInfo[$board_id]['comment']['title'] ?></a><br><?= $nodeInfo[$board_id]['comment']['username'] ?> <span class='time'><?= $nodeInfo[$board_id]['comment']['create_time'] ?></span><?php endif ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  <?php endforeach ?>

  <?php if ($city === City::HOUSTON || $city === City::DALLAS) : ?>
    <div style="margin-top:15px; clear: both;">
      <div style="padding:0.4em 0.7em;">友情链接</div>
      <div style="padding:0.4em 0.7em; background:#EEEEEE none repeat scroll 0 0;">
        <a style="padding: 4px;" target="_blank" href="https://www.bayever.com">生活在湾区</a>
        <?php if ($city === City::HOUSTON) : ?>
          <a style="padding: 4px;" target="_blank" href="https://www.dallasbbs.com">缤纷达拉斯</a>
        <?php elseif ($city === City::DALLAS) : ?>
          <a style="padding: 4px;" target="_blank" href="https://www.houstonbbs.com">缤纷休斯顿</a>
        <?php endif ?>
      </div>
    </div>
  <?php endif ?>

<?php
};
