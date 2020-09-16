<?php declare(strict_types=1);

namespace site\handler\term;

use lzx\html\Template;
use site\Controller;

class Handler extends Controller
{
    public function run(): void
    {
        $this->cache = $this->getPageCache();

        $sitename = [
            'site_zh_cn' => self::$city->domain === 'bayever.com' ? '生活在湾区' : '缤纷' . self::$city->nameZh,
            'site_en_us' => self::$city->domain
        ];

        $this->var['content'] = new Template('term', $sitename);
    }
}
