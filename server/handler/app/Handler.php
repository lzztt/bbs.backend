<?php declare(strict_types=1);

namespace site\handler\app;

use Exception;
use site\Controller;

class Handler extends Controller
{
    public function run(): void
    {
        $app = $this->args[0];
        if (!$app) {
            $this->pageNotFound();
        }
        $this->response->setContent(file_get_contents($this->getLatestVersion($app) . '/index.html'));
    }

    protected function getLatestVersion(string $app): string
    {
        $versionFile = $this->config->path['file'] . '/app/' . $app . '.current';
        $current = is_file($versionFile) ? file_get_contents($versionFile) : null;

        if ($current) {
            $dir = $this->config->path['file'] . '/app/' . $current;
            if (is_dir($dir)) {
                return $dir;
            }
        }

        // need to search
        $dirs = glob($this->config->path['file'] . '/app/' . $app . '.*', GLOB_ONLYDIR);
        // not found
        if (!$dirs) {
            $this->pageNotFound();
        }

        $count = count($dirs);
        $dir = $count == 1 ? $dirs[0] : $dirs[$count - 2];

        // cache the latest version
        try {
            file_put_contents($versionFile, basename($dir));
        } catch (Exception $e) {
            // ignore and continue
        }

        return $dir;
    }
}
