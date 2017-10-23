<?php

namespace site\controller\search;

use site\controller\Search;
use lzx\cache\PageCache;

class SearchCtrler extends Search
{
    public function run()
    {
        $this->cache = new PageCache($this->request->uri);

        $searchEngineIDs = ['houston' => 'ff_lfzbzonw', 'dallas' => 'gznplywzy7a', 'austin' => 'ihghalygyj8'];
        $seid = $searchEngineIDs[self::$_city->uriName];

        $html = <<<HTML
<div id="cse-search-form">Loading</div>
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
.cse input.gsc-input,
td.gsc-input,
input.gsc-input {
background-image:none !important;
}
div.gsc-tabsArea,
div.gs-visibleUrl {
display:none !important;
}
</style>
<script src="https://www.google.com/jsapi" type="text/javascript"></script>
<script type="text/javascript">
  google.load('search', '1', {language : 'en'});
  google.setOnLoadCallback(function() {
     var customSearchControl = new google.search.CustomSearchControl('011972505836335581212:$seid');
     customSearchControl.setResultSetSize(google.search.Search.FILTERED_CSE_RESULTSET);
     var options = new google.search.DrawOptions();
     options.setSearchFormRoot('cse-search-form');

     options.setAutoComplete(true);
     customSearchControl.draw('cse', options);
  }, true);
</script>
<link rel="stylesheet" href="https://www.google.com/cse/style/look/greensky.css" />

<div id="cse"></div>
HTML;

        $this->_var['content'] = $html;
    }
}

//__END_OF_FILE__
