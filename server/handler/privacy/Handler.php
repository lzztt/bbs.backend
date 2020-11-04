<?php

declare(strict_types=1);

namespace site\handler\privacy;

use site\Controller;
use site\gen\theme\roselife\Privacy;

class Handler extends Controller
{
    public function run(): void
    {
        $this->cache = $this->getPageCache();

        $this->html->setContent(
            (new Privacy())
                ->setSiteEnUs(self::$city->domain)
                ->setSiteZhCn(self::$city->domain === 'bayever.com' ? '生活在湾区' : '缤纷' . self::$city->nameZh)
        );
    }
}
