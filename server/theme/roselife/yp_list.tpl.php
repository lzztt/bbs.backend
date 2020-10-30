<?php

use lzx\html\Template;

function (
  string $ajaxUri,
  array $nodes,
  Template $pager
) {
?>

  <header class="content_header">
    <?= $pager ?>
  </header>

  <?php if ($nodes) : ?>
    <div class="ajax_load" data-ajax='<?= $ajaxUri ?>'>
      <style>
        .bcard {
          display: inline-block;
          width: 95%;
          max-width: min(100vw, 400px);
          margin: 0.5em;
          border: 1px solid #006666;
          vertical-align: top;
        }

        .bcard header {
          background-color: gold;
          text-align: center;
          padding: 0.2em;
        }

        .bcard [data-before] {
          position: relative;
          padding: 0.1em;
          padding-left: 5em;
        }

        .bcard [data-before]:before {
          content: attr(data-before);
          display: inline-block;
          position: absolute;
          left: 0;
          width: 4.5em;
          text-align: right;
          padding-right: 0.5em;
          color: #006666;
        }

        .bcard footer {
          text-align: right;
          font-size: 0.929em;
          color: gray;
        }
      </style>
      <?php foreach ($nodes as $n) : ?>
        <div class="bcard">
          <header><a title="<?= $n['title'] ?>" href="/node/<?= $n['id'] ?>"><?= $n['title'] ?></a></header>
          <div>
            <div data-before='地址'><?= $n['address'] ?></div>
            <div data-before='电话'><?= $n['phone'] ?></div>
            <?php if (isset($n['fax'])) : ?>
              <div data-before='传真'><?= $n['fax'] ?></div>
            <?php endif ?>
            <?php if (isset($n['email'])) : ?>
              <div data-before='电子邮箱'><?= $n['email'] ?></div>
            <?php endif ?>
            <?php if (isset($n['website'])) : ?>
              <div data-before='网站'><?= $n['website'] ?></div>
            <?php endif ?>
          </div>
          <footer><span class="ajax_viewCount<?= $n['id'] ?>"></span>次浏览，<?= $n['rating_count'] ?>人评分，<?= $n['comment_count'] ?>条评论</footer>
        </div>
      <?php endforeach ?>
    </div>
  <?php endif ?>
  <?= $pager ?>

<?php
};
