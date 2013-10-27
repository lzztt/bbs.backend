<?php

namespace site\controller;

use lzx\core\Controller;
use lzx\html\Template;

class Search extends Controller
{

   public function run()
   {
      $page = $this->loadController('Page');
      $page->updateInfo();
      $page->setPage();

      $html = <<<HTML
<div id="cse-search-form" style="width: 100%;">Loading</div>
<style>
.gsc-search-box tbody {
border: none !important;
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
<script src="http://www.google.com/jsapi" type="text/javascript"></script>
<script type="text/javascript">
  google.load('search', '1', {language : 'en'});
  google.setOnLoadCallback(function() {
    var customSearchControl = new google.search.CustomSearchControl('011972505836335581212:ff_lfzbzonw');
    customSearchControl.setResultSetSize(google.search.Search.FILTERED_CSE_RESULTSET);
    var options = new google.search.DrawOptions();
    options.setSearchFormRoot('cse-search-form');

    options.setAutoComplete(true);
    customSearchControl.draw('cse', options);
  }, true);
</script>
<link rel="stylesheet" href="http://www.google.com/cse/style/look/greensky.css" />

<div id="cse" style="width:90%;"></div>
HTML;

      $this->html->var['content'] = $html;
   }

}

//__END_OF_FILE__
