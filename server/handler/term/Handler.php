<?php

declare(strict_types=1);

namespace site\handler\term;

use site\City;
use site\Controller;
use site\gen\theme\roselife\Term;

class Handler extends Controller
{
    public function run(): void
    {
        $this->cache = $this->getPageCache();

        $this->html->setContent(
            (new Term())
                ->setSiteEnUs(self::$city->domain)
                ->setSiteZhCn(self::$city->id === City::SFBAY ? '生活在湾区' : '缤纷' . self::$city->nameZh)
        );
    }
}
