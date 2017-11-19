<?php declare(strict_types=1);

namespace site;

use site\WebApp;

if (PHP_SAPI === 'cli') {
     $_SERVER['HTTP_HOST'] = 'www.longzox.com';
     $opts = getopt('l::');
     $_SERVER['REQUEST_URI'] = $opts['l'] ? $opts['l'] : '/';
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

$app = new WebApp();
$app->run();
