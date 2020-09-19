<?php declare(strict_types=1);

namespace site\handler\yp\join;

use site\Controller;
use site\gen\theme\roselife\YpJoin;

class Handler extends Controller
{
    public function run(): void
    {
        $this->cache = $this->getPageCache();

        $this->html
            ->setHeadTitle('市场推广 先入为主')
            ->setContent(new YpJoin());
    }
}
