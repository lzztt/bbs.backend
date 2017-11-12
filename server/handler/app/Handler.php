<?php

namespace site\handler\app;

use site\Controller;

class Handler extends Controller
{
    public function run()
    {
        $app = $this->args[0];
        $this->response->setContent(file_get_contents($this->getLatestVersion($app) . '/index.html'));
    }

    protected function getLatestVersion($app)
    {
        $current = null;
        try {
            $current = file_get_contents($this->config->path['file'] . '/app/' . $app . '.current');
        } catch (\Exception $ex) {
            // ignore and continue
        }

        if ($current) {
            $dir = $this->config->path['file'] . '/app/' . $current;
            if (is_dir($dir)) {
                return $dir;
            }
        }

        // need to search
        $dirs = glob($this->config->path['file'] . '/app/' . $app . '.*', \GLOB_ONLYDIR);
        // not found
        if (!$dirs) {
            $this->pageNotFound();
        }

        $count = count($dirs);
        $dir = $count == 1 ? $dirs[0] : $dirs[$count - 2];

        // cache the latest version
        try {
            file_put_contents($this->config->path['file'] . '/app/' . $app . '.current', basename($dir));
        } catch (\Exception $ex) {
            // ignore and continue
        }

        return $dir;
    }
}
