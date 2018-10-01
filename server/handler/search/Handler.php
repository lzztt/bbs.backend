<?php declare(strict_types=1);

namespace site\handler\search;

use lzx\cache\PageCache;
use site\Controller;

class Handler extends Controller
{
    public function run(): void
    {
        $this->cache = new PageCache($this->request->uri);

        $searchEngineIDs = ['houstonbbs.com' => 'ff_lfzbzonw', 'dallasbbs.com' => 'gznplywzy7a', 'austinbbs.com' => 'ihghalygyj8', 'bayever.com' => 'vx3u09xj83w'];
        $seid = $searchEngineIDs[self::$city->domain];

        $html = <<<HTML
<style>
.gsc-search-box {
  width: auto !important;
}
tbody, tr {
  border: none !important;
}
.gsc-search-box td {
  width: auto !important;
  padding: 0.3em !important;
}
input.gsc-input
{
  min-width: 200px;
}
td.gsc-input,
input.gsc-input {
  background-image: none !important;
  text-indent: 0px !important;
}
button {
  height: auto !important;
}
</style>
<script>
  (function() {
    var cx = '011972505836335581212:$seid';
    var gcse = document.createElement('script');
    gcse.type = 'text/javascript';
    gcse.async = true;
    gcse.src = 'https://cse.google.com/cse.js?cx=' + cx;
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(gcse, s);
  })();
</script>
<gcse:search></gcse:search>
HTML;

        $this->var['content'] = $html;
    }
}
