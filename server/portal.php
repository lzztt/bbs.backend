<?php declare(strict_types=1);

namespace site;

use site\WebApp;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$app = new WebApp();
$app->run();
