<?php

declare(strict_types=1);

namespace site\handler\search;

use lzx\html\Template;
use site\City;
use site\Controller;

class Handler extends Controller
{
    public function run(): void
    {
        $this->cache = $this->getPageCache();

        $seid = match (self::$city->id) {
            City::HOUSTON => 'f181bd1f07d3699da',
            City::DALLAS => '0173f1aadfdbf13f5',
            City::SFBAY => '666eb4297624b23f4',
        };

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
.gcsc-more-maybe-branding-root {
 display: none !important;
}
</style>
<script async src="https://cse.google.com/cse.js?cx=$seid"></script>
<div class="gcse-search"></div>
HTML;

        $this->html->setContent(Template::fromStr($html));
    }
}
