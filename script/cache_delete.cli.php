<?php declare(strict_types=1);

namespace Site;

use Lzx\Core\ClassLoader;
use Lzx\Core\Handler;
use Lzx\Core\Config;
use Lzx\Core\Logger;
use Lzx\Core\MySQL;
use Lzx\Core\Mailer;
use Lzx\Core\Template;
use Lzx\Core\Cache;
use Site\DataObject\User;

mb_internal_encoding("UTF-8");

// note: cache path in php and nginx are using servername
$_SERVER['SERVER_NAME'] = 'www.houstonbbs.com';

$domain = 'houstonbbs.com';
$siteDir = dirname(__DIR__);
$path = [
    'lzx' => $siteDir . '/Lzx',
    'root' => $siteDir,
    'log' => $siteDir . '/logs',
    'theme' => $siteDir . '/themes',
    'backup' => $siteDir . '/backup',
];

require_once $path['lzx'] . '/Core/ClassLoader.php';
$loader = ClassLoader::getInstance();
$loader->registerNamespace('Lzx', $path['lzx']);
$loader->registerNamespace(__NAMESPACE__, $siteDir);

// set ErrorHandler
Handler::setErrorHandler();

$logger = Logger::getInstance($path['log']);
$logger->userAgent = 'cli';

Handler::$logger = $logger;
// set ExceptionHandler
Handler::setExceptionHandler();

// load site config and class config
$config = Config::getInstance($siteDir . '/config.php');

$cacheKeys = array_slice($argv, 1);

if (sizeof($cacheKeys) < 1) {
    echo 'usage: php ' . $argv[0] . ' <cacheKeys> ...' . PHP_EOL;
    exit;
}

$cache = Cache::getInstance($config->cache_path);
$cache->setLogger($logger);
foreach ($cacheKeys as $k) {
    echo 'deleting ' . $k . PHP_EOL;
    $cache->delete($k);
}
