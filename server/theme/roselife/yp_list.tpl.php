<header class="content_header">
  <div class="breadcrumb"><?= $breadcrumb ?></div>
  <div class="taxonomy-term-description"><?= $cateDescription ?></div>
  <?= $pager ?>
</header>

<?php if ($nodes): ?>
  <div class="ajax_load" data-ajax='<?= $ajaxURI ?>'>
    <?php foreach ($nodes as $n): ?>
      <div class="bcard">
        <header><a title="<?= $n['title'] ?>" href="/node/<?= $n['id'] ?>"><?= $n['title'] ?></a></header>
        <ul class='clean'>
          <li data-before='地址'><?= $n['address'] ?></li>
          <li data-before='电话'><?= $n['phone'] ?></li>
          <?php if (isset($n['fax'])): ?>
            <li data-before='传真'><?= $n['fax'] ?></li>
          <?php endif ?>
          <?php if (isset($n['email'])): ?>
            <li data-before='电子邮箱'><?= $n['email'] ?></li>
          <?php endif ?>
          <?php if (isset($n['website'])): ?>
            <li data-before='网站'><?= $n['website'] ?></li>
          <?php endif ?>
        </ul>
        <footer><span class="ajax_viewCount<?= $n['id'] ?>"></span>次浏览，<?= $n['rating_count'] ?>人评分，<?= $n['comment_count'] ?>条评论</footer>
      </div>
    <?php endforeach ?>
  </div>
<?php endif ?>
<?= $pager ?>