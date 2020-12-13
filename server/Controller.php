<?php

declare(strict_types=1);

namespace site;

use lzx\core\Logger;
use lzx\core\Request;
use lzx\core\Response;
use lzx\html\Template;
use site\Config;
use site\Handler;
use site\Session;
use site\dbobject\Tag;
use site\gen\theme\roselife\Html;

abstract class Controller extends Handler
{
    protected Html $html;

    public function __construct(Request $req, Response $response, Config $config, Logger $logger, Session $session, array $args)
    {
        parent::__construct($req, $response, $config, $logger, $session, $args);

        $this->html = new Html();
        $this->response->setContent($this->html);
    }

    public function afterRun(): void
    {
        $html = $this->html;
        $html->setCity(self::$city->id)
            ->setDebug($this->config->mode === Config::MODE_DEV);

        // set navbar
        $navbarCache = $this->getIndependentCache('page_navbar');
        $navbar = $navbarCache->getData();
        if (!$navbar) {
            $navbar = Template::fromStr($this->createMenu(self::$city->tidForum));
            $navbarCache->setData($navbar);
        }
        $html->setPageNavbar($navbar);

        // set headers
        $siteName = self::$city->domain === 'bayever.com' ? '生活在湾区' : '缤纷' . self::$city->nameZh;
        if (empty($html->getHeadTitle())) {
            $html->setHeadTitle($siteName);
        }

        if (empty($html->getHeadDescription())) {
            $html->setHeadDescription(self::$city->nameZh . ' 华人 论坛 租房 旅游 黄页 移民 周末活动 单身 交友 ' . self::$city->nameEn . ' Chinese Forum');
        } else {
            $html->setHeadDescription($html->getHeadDescription() . ' ' . self::$city->nameZh . ' 华人 论坛 ' . self::$city->nameEn . ' Chinese Forum');
        }

        if (empty($html->getLastModifiedTime())) {
            $html->setLastModifiedTime($this->request->timestamp);
        }
    }

    protected function createMenu(int $tid): string
    {
        $tag = new Tag($tid, 'id');
        $tree = $tag->getTagTree();
        return json_encode(
            array_map(
                function ($i) {
                    return array_intersect_key($i, ['id' => null, 'name' => null]);
                },
                array_values(array_filter($tree, function ($i) {
                    return !array_key_exists('children', $i);
                }))
            ),
            JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}
