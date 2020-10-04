<?php

use lzx\html\Template;
use site\Config;

function (
  int $city,
  Template $content,
  string $head_description,
  string $head_title,
  string $min_version,
  Template $page_navbar,
  string $sitename,
  bool $debug,
  string $theme
) {
?>

<?php require(Config::getInstance()->path['file'] . '/index.html'); ?>

<?php
};
