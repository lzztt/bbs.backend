<?php

use lzx\html\Template;
use site\Config;

function (
  int $city,
  int $lastModifiedTime,
  Template $content,
  string $headTitle,
  string $headDescription,
  Template $pageNavbar,
  bool $debug
) {
?>

<?php require(Config::getInstance()->path['file'] . '/index.html'); ?>

<?php
};
