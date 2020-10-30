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
    <style>
      .bcards {
        margin: 0.5rem;
        display: grid;
        grid-gap: 0.5rem;
        grid-template-columns: repeat(auto-fill, minmax(min(400px, 100%), 1fr));
      }
    </style>
    <div class="ajax_load bcards" data-ajax='<?= $ajaxUri ?>'>
      <style>
        .bcard {
          border: 1px solid #006666;
        }

        .bcard header {
          background-color: gold;
          text-align: center;
          padding: 0.2rem;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }

        .bcard div {
          display: grid;
          grid-template-columns: 4.5rem auto;
          grid-gap: 0.25rem;
        }

        .bcard div span:nth-child(odd) {
          text-align: right;
          color: #006666;
        }

        .bcard footer {
          text-align: right;
          font-size: 0.929rem;
          color: gray;
        }
      </style>
      <?php foreach ($nodes as $n) : ?>
        <div class="bcard">
          <header><a title="<?= $n['title'] ?>" href="/node/<?= $n['id'] ?>"><?= $n['title'] ?></a></header>
          <div>
            <span>地址</span><span><?= $n['address'] ?></span>
            <span>电话</span><span><?= $n['phone'] ?></span>
            <?php if (isset($n['fax'])) : ?>
              <span>传真</span><span><?= $n['fax'] ?></span>
            <?php endif ?>
            <?php if (isset($n['email'])) : ?>
              <span>电子邮箱</span><span><?= $n['email'] ?></span>
            <?php endif ?>
            <?php if (isset($n['website'])) : ?>
              <span>网站</span><span><?= $n['website'] ?></span>
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
