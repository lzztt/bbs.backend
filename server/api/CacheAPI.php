<?php

namespace site\api;

use site\Service;
use lzx\cache\CacheHandler;

class CacheAPI extends Service
{
    /**
     * get ads
     * uri: /api/cache[/<cache_dir>]
     */
    public function get()
    {
        if ($this->request->uid != 1 || \in_array('..', $this->args)) {
            $this->forbidden();
        }

        $cacheList = \scandir(CacheHandler::$path . '/' . \implode('/', $this->args));
        $this->_json($cacheList);
    }

    /**
     * remove one file from cache
     * uri: /api/cache/<cache_file>?action=delete
     */
    public function delete()
    {
        if ($this->request->uid != 1 || empty($this->args) || \in_array('..', $this->args)) {
            $this->forbidden();
        }

        $cacheName = \implode('/', $this->args);
        if (\substr($cacheName, 0, 5) === 'page/') {
            $cacheName = urldecode(\substr($cacheName, 4)); // get page cache key, including the leading slash
        } elseif (\substr($cacheName, 0, 8) === 'segment/') {
            $cacheName = \substr($cacheName, 8); // get segment cache key, excluding teh leading slash
        } else {
            $this->error('wrong cache type');
        }

        $cache = $this->_getIndependentCache($cacheName);
        $cache->delete();

        $this->_json(['cacheName' => $cacheName]);
    }
}
