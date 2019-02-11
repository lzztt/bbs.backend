<!DOCTYPE html>
<html lang="zh" dir="ltr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
  <body>
    <article>
        [<?= $type ?>] <a style="text-decoration:none;" href="http://www.<?= $site ?>.com/forum.php?mod=viewthread&tid=<?= $tid ?>&action=printable">by <?= $author ?></a>
        @ <?= $createTime ?><br>
        <h2><?= $title ?></h2>
        <p><?= nl2br($body) ?></p>
        <?php foreach($images as $i): ?>
            <?= $i['name'] ?><br>
            <img src="<?= $i['url'] ?>"><br>
        <?php endforeach; ?>
      <br>
    </article>
  </body>
</html>
