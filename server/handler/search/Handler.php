<?php declare(strict_types=1);

namespace site\handler\search;

use lzx\html\Template;
use site\Controller;

class Handler extends Controller
{
  public function run(): void
  {
    $this->cache = $this->getPageCache();

    $searchEngineIDs = [
      'houstonbbs.com' => 'ff_lfzbzonw',
      'dallasbbs.com' => 'gznplywzy7a',
      'bayever.com' => 'vx3u09xj83w'
    ];
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
td:first-child {
    background: white none no-repeat !important;
}
button {
  height: 13px !important;
}
</style>
<script async src="https://cse.google.com/cse.js?cx=011972505836335581212:$seid"></script>
<div class="gcse-search"></div>
HTML;

    $this->html->setContent(Template::fromStr($html));
  }
}
