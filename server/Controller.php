<?php declare(strict_types=1);

namespace site;

use Exception;

// only controller will handle all exceptions and local languages
// other classes will report status to controller
// controller set status back the WebApp object
// WebApp object will call Theme to display the content
use lzx\core\Controller as LzxCtrler;
use lzx\core\Request;
use lzx\core\Response;
use lzx\html\Template;
use site\Config;
use lzx\core\Logger;
use site\Session;
use site\ControllerFactory;
use site\dbobject\User;
use site\dbobject\Tag;
use site\dbobject\SecureLink;
use lzx\cache\CacheEvent;
use lzx\cache\CacheHandler;
use site\dbobject\City;

/**
 *
 * @property \lzx\core\Logger $logger
 * @property \lzx\core\Request $request
 * @property \site\Session $session
 * @property \site\Config $config
 * @property \lzx\cache\PageCache $cache
 * @property \lzx\cache\Cache[]  $independentCacheList
 * @property \lzx\cache\CacheEvent[] $cacheEvents
 * @property \site\dbobject\City $city
 *
 */
abstract class Controller extends LzxCtrler
{
    const UID_GUEST = 0;
    const UID_ADMIN = 1;

    protected static $city;
    private static $staticInitialized = false;
    private static $cacheHandler;
    public $args;
    public $id;
    public $config;
    public $cache;
    public $site;
    public $session;
    protected $var = [];
    protected $independentCacheList = [];
    protected $cacheEvents = [];

    /**
     * public methods
     */
    public function __construct(Request $req, Response $response, Config $config, Logger $logger, Session $session)
    {
        parent::__construct($req, $response, $logger);
        $this->session = $session;

        if (!self::$staticInitialized) {
            $this->staticInit();
            self::$staticInitialized = true;
        }

        $this->config = $config;

        // register this controller as an observer of the HTML template
        $html = new Template('html');
        $html->attach($this);
        $this->response->setContent($html);
    }

    private function staticInit()
    {
        // set site info
        $site = preg_replace(['/\w*\./', '/bbs.*/'], '', $this->request->domain, 1);

        Template::setSite($site);

        self::$cacheHandler = CacheHandler::getInstance();
        self::$cacheHandler->setCacheTreeTable(self::$cacheHandler->getCacheTreeTable() . '_' . $site);
        self::$cacheHandler->setCacheEventTable(self::$cacheHandler->getCacheEventTable() . '_' . $site);

        // validate site for session
        self::$city = new City();
        self::$city->uriName = $site;
        self::$city->load();
        if (self::$city->exists()) {
            if (self::$city->id != $this->session->getCityID()) {
                $this->session->setCityID(self::$city->id);
            }
        } else {
            $this->error('unsupported website: ' . $this->request->domain);
        }

        // update user info
        if ($this->request->uid > 0) {
            $user = new User($this->request->uid, null);
            // update access info
            $user->call('update_access_info(' . $this->request->uid . ',' . $this->request->timestamp . ',"' . $this->request->ip . '")');
        }
    }

    public function flushCache()
    {
        if ($this->config->cache) {
            if ($this->cache && $this->response->getStatus() < 300 && Template::hasError() === false) {
                $this->response->cacheContent($this->cache);
                $this->cache->flush();
                $this->cache = null;
            }

            foreach ($this->independentCacheList as $s) {
                $s->flush();
            }

            foreach ($this->cacheEvents as $e) {
                $e->flush();
            }
        }
    }

    /**
     * Observer design pattern interface
     */
    public function update(Template $html)
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

            $navbar = new Template('page_navbar', $vars);
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
        $html->detach($this);
    }

    protected function ajax($return)
    {
        $json = json_encode($return, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            $json = '{"error":"ajax json encode error"}';
        }
        $this->response->type = Response::JSON;
        $this->response->setContent($json);
    }

    /**
     *
     * @return \lzx\cache\Cache
     */
    protected function getIndependentCache($key)
    {
        $key = self::$cacheHandler->getCleanName($key);
        if (array_key_exists($key, $this->independentCacheList)) {
            return $this->independentCacheList[$key];
        } else {
            $cache = self::$cacheHandler->createCache($key);
            $this->independentCacheList[$key] = $cache;
            return $cache;
        }
    }

    /**
     *
     * @return \lzx\cache\CacheEvent
     */
    protected function getCacheEvent($name, $objectID = 0)
    {
        $name = self::$cacheHandler->getCleanName($name);
        $objID = (int) $objectID;
        if ($objID < 0) {
            $objID = 0;
        }

        $key = $name . $objID;
        if (array_key_exists($key, $this->cacheEvents)) {
            return $this->cacheEvents[$key];
        } else {
            $event = new CacheEvent($name, $objID);
            $this->cacheEvents[$key] = $event;
            return $event;
        }
    }

    protected function forward($uri)
    {
        $newReq = clone $this->request;
        $newReq->uri = $uri;
        $ctrler = ControllerFactory::create($newReq, $this->response, $this->config, $this->logger, $this->session);
        $ctrler->request = $this->request;
        $ctrler->run();
    }

    protected function displayLogin()
    {
        $this->response->pageRedirect('/app/user/login');
    }

    protected function createSecureLink($uid, $uri)
    {
        $slink = new SecureLink();
        $slink->uid = $uid;
        $slink->time = $this->request->timestamp;
        $slink->code = rand();
        $slink->uri = $uri;
        $slink->add();
        return $slink;
    }

    /**
     *
     * @param type $uri
     * @return \site\dbobject\SecureLink|null
     */
    protected function getSecureLink($uri)
    {
        $arr = explode('?', $uri);
        if (sizeof($arr) == 2) {
            $l = new SecureLink();

            $l->uri = $arr[0];
            parse_str($arr[1], $get);

            if (isset($get['r']) && isset($get['u']) && isset($get['c']) && isset($get['t'])) {
                $l->id = $get['r'];
                $l->uid = $get['u'];
                $l->code = $get['c'];
                $l->time = $get['t'];
                $l->load('id');
                if ($l->exists()) {
                    return $l;
                }
            }
        }

        return null;
    }

    /*
     * create menu tree for root tags
     */

    protected function createMenu($tid)
    {
        $tag = new Tag($tid, null);
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

    protected function getPagerInfo($nTotal, $nPerPage)
    {
        if ($nPerPage <= 0) {
            throw new Exception('invalid value for number of items per page: ' . $nPerPage);
        }

        $pageCount = $nTotal > 0 ? ceil($nTotal / $nPerPage) : 1;
        if ($this->request->get['p']) {
            if ($this->request->get['p'] === 'l') {
                $pageNo = $pageCount;
            } elseif (is_numeric($this->request->get['p'])) {
                $pageNo = (int) $this->request->get['p'];

                if ($pageNo < 1 || $pageNo > $pageCount) {
                    $this->pageNotFound();
                }
            } else {
                $this->pageNotFound();
            }
        } else {
            $pageNo = 1;
        }

        return [$pageNo, $pageCount];
    }
}
