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

abstract class Controller extends Handler
{
    use HandlerTrait;

    const UID_GUEST = 0;
    const UID_ADMIN = 1;

    public $args;
    public $id;
    public $config;
    public $cache;
    public $site;
    public $session;
    protected $var = [];

    public function __construct(Request $req, Response $response, Config $config, Logger $logger, Session $session)
    {
        parent::__construct($req, $response, $logger);
        $this->session = $session;
        $this->config = $config;
        $this->staticInit();

        // register this controller as an observer of the HTML template
        $html = new Template('html');
        $html->onBeforeRender([$this, 'update']);
        $this->response->setContent($html);
    }

    // interface for Observer design pattern
    public function update(Template $html): void
    {
        // set navbar
        $navbarCache = $this->getIndependentCache('page_navbar');
        $navbar = $navbarCache->fetch();
        if (!$navbar) {
            if (self::$city->tidYp) {
                $vars = [
                    'forumMenu' => $this->createMenu(self::$city->tidForum),
                    'ypMenu' => $this->createMenu(self::$city->tidYp),
                    'uid' => $this->request->uid
                ];
            } else {
                $vars = [
                    'forumMenu' => $this->createMenu(self::$city->tidForum),
                    'uid' => $this->request->uid
                ];
            }

            $navbar = (string) new Template('page_navbar', $vars);
            $navbarCache->store($navbar);
        }
        $this->var['page_navbar'] = $navbar;

        // set headers
        if (!$this->var['head_title']) {
            $this->var['head_title'] = '缤纷' . self::$city->name . '华人网';
        }

        if (!$this->var['head_description']) {
            $this->var['head_description'] = self::$city->name . ' 华人 旅游 黄页 移民 周末活动 单身 交友 ' . ucfirst(self::$city->uriName) . ' Chinese ' . self::$city->uriName . 'bbs';
        } else {
            $this->var['head_description'] = $this->var['head_description'] . ' ' . self::$city->name . ' 华人 ' . ucfirst(self::$city->uriName) . ' Chinese ' . self::$city->uriName . 'bbs';
        }
        $this->var['sitename'] = '缤纷' . self::$city->name;

        // set min version for css and js
        if (!Template::$debug) {
            $min_version = $this->config->path['file'] . '/themes/' . Template::$theme . '/min/min.current';
            if (file_exists($min_version)) {
                $this->var['min_version'] = file_get_contents($min_version);
            }
        }

        // populate template variables and remove self as an observer
        $html->setVar($this->var);
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
