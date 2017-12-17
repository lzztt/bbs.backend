<?php declare(strict_types=1);

namespace site\handler\search;

use lzx\cache\PageCache;
use site\Controller;

class Handler extends Controller
{
    public function run(): void
    {
        $this->cache = new PageCache($this->request->uri);

        $searchEngineIDs = ['houston' => 'ff_lfzbzonw', 'dallas' => 'gznplywzy7a', 'austin' => 'ihghalygyj8'];
        $seid = $searchEngineIDs[self::$city->uriName];

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
input[type=text].gsc-input
{
min-width: 200px;
}
td.gsc-input,
input.gsc-input {
background-image:none !important;
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
<gcse:searchbox></gcse:searchbox>

<gcse:searchresults></gcse:searchresults>
HTML;

        $this->var['content'] = $html;
    }
}
