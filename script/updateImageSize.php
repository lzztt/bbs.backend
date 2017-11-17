<?php declare(strict_types=1);

namespace site;

use Exception;
use lzx\App;
use lzx\core\MySQL;
use site\dataobject\Image;

$SERVERDIR = dirname(__DIR__);
require_once __DIR__ . '/../../lib/lzx/App.php';

class Script extends App
{
    public function run($argc, array $argv)
    {
        $db = MySQL::getInstance($this->config->database, true);

        $filePath = $this->config->path['file'];

        $img = new Image();
        $arr = $img->getList('fid,path');

        foreach ($arr as $i) {
            try {
                $info = getimagesize($filePath . $i['path']);
                $width = intval($info[0]);
                $height = intval($info[1]);
                if ($height > 0 && $width > 0) {
                    $db->query('UPDATE Image SET height = ' . $height . ', width = ' . $width . ' WHERE fid = ' . $i['fid']);
                } else {
                    echo $i['fid'] . PHP_EOL;
                }
            } catch (Exception $e) {
                echo $i['fid'] . PHP_EOL;
                $this->logger->error($e->getMessage());
                continue;
            }
        }
    }
}

$app = new Script($SERVERDIR . '/config.php', [__NAMESPACE__ => $SERVERDIR]);
$app->run($argc, $argv);
