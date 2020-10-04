<?php declare(strict_types=1);

namespace site;

use lzx\core\Handler;
use lzx\core\Logger;
use lzx\core\Request;
use lzx\core\Response;
use lzx\html\Template;
use site\Config;
use site\HandlerTrait;
use site\Session;
use site\dbobject\Tag;
use site\gen\theme\roselife\Html;
use site\gen\theme\roselife\PageNavbar;

abstract class Controller extends Handler
{
    use HandlerTrait;

    const UID_GUEST = 0;
    const UID_ADMIN = 1;

    public $args;
    public $id;
    public $config;
    public $site;
    public $session;
    protected $var = [];
    protected Html $html;

    public function __construct(Request $req, Response $response, Config $config, Logger $logger, Session $session, array $args)
    {
        parent::__construct($req, $response, $logger);
        $this->session = $session;
        $this->config = $config;
        $this->args = $args;
        $this->staticInit();

        $this->html = new Html();
        $this->response->setContent($this->html);
    }

    public function afterRun(): void
    {
        $html = $this->html;
        $html->setCity(self::$city->id)
            ->setDebug($this->config->mode === Config::MODE_DEV)
            ->setTheme($this->config->theme);

        // set navbar
        $navbarCache = $this->getIndependentCache('page_navbar');
        $navbar = $navbarCache->getData();
        if (!$navbar) {
            $navbar = (new PageNavbar())
                ->setForumMenu(Template::fromStr($this->createMenu(self::$city->tidForum)));
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
        $html->setSitename($siteName);

        // set min version for css and js
        if (!$html->getDebug()) {
            $min_version = $this->config->path['file'] . '/themes/' . $html->getTheme() . '/min/min.current';
            if (file_exists($min_version)) {
                $html->setMinVersion(file_get_contents($min_version));
            }
        }
    }

    protected function createMenu(int $tid): string
    {
        $tag = new Tag($tid, 'id');
        $tree = $tag->getTagTree();
        $type = 'tag';
        $root_id = array_shift(array_keys($tag->getTagRoot()));
        if (self::$city->tidForum == $root_id) {
            $type = 'forum';
        } elseif (self::$city->tidYp == $root_id) {
            $type = 'yp';
        }
        $liMenu = '';

        if (sizeof($tree) > 0) {
            foreach ($tree[$tid]['children'] as $branch_id) {
                $branch = $tree[$branch_id];
                $liMenu .= '<li><a title="' . $branch['name'] . '" href="/' . $type . '/' . $branch['id'] . '">' . $branch['name'] . '</a>';
                if (sizeof($branch['children'])) {
                    $liMenu .= '<ul style="display: none;">';
                    foreach ($branch['children'] as $leaf_id) {
                        $leaf = $tree[$leaf_id];
                        $liMenu .= '<li><a title="' . $leaf['name'] . '" href="/' . $type . '/' . $leaf['id'] . '">' . $leaf['name'] . '</a></li>';
                    }
                    $liMenu .= '</ul>';
                }
                $liMenu .= '</li>';
            }
        }

        return $liMenu;
    }
}
