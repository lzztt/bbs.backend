<?php declare(strict_types=1);

namespace site\handler\help;

use site\Controller;
use site\gen\theme\roselife\Help;

class Handler extends Controller
{
    public function run(): void
    {
        $this->html->setContent(
            (new Help())
                ->setCity(self::$city->id)
        );
    }
}
