<?php declare(strict_types=1);

namespace site;

use lzx\core\Service as LzxService;
use lzx\core\Request;
use lzx\core\Response;
use lzx\core\Logger;
use lzx\core\Mailer;
use lzx\html\Template;
use site\Session;
use lzx\cache\CacheHandler;
use lzx\cache\CacheEvent;
use site\Config;
use site\dbobject\City;
use site\dbobject\User;

// handle RESTful web API
// resource uri: /api/<resource>&action=[get,post,put,delete]

/**
 * @property \site\Session $session
 * @property \site\dbobject\City $city
 */
abstract class Service extends LzxService
{
    const UID_GUEST = 0;
    const UID_ADMIN = 1;

    protected static $city;
    private static $actions = ['get', 'post', 'put', 'delete'];
    private static $staticInitialized = false;
    private static $cacheHandler;
    public $action;
    public $args;
    public $session;
    private $independentCacheList = [];
    private $cacheEvents = [];

    public function __construct(Request $req, Response $response, Config $config, Logger $logger, Session $session)
    {
        parent::__construct($req, $response, $logger);
        $this->session = $session;
        $this->config = $config;

        if (!self::$staticInitialized) {
            $this->staticInit();
            self::$staticInitialized = true;
        }
    }

    private function staticInit()
    {
        // set site info
        $site = preg_replace(['/\w*\./', '/bbs.*/'], '', $this->request->domain, 1);

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
    }

    public function run()
    {
        if (array_key_exists('action', $this->request->get)
                && in_array($this->request->get['action'], self::$actions)) {
            $action = $this->request->get['action'];
        } else {
            $action = ($this->request->post || $this->request->json) ? 'post' : 'get';
        }
        $this->$action();
    }

    // RESTful get
    public function get()
    {
        $this->forbidden();
    }

    // RESTful post
    public function post()
    {
        $this->forbidden();
    }

    // RESTful put
    public function put()
    {
        $this->forbidden();
    }

    // RESTful delete
    public function delete()
    {
        $this->forbidden();
    }

    public function flushCache()
    {
        $config = Config::getInstance();
        if ($config->cache) {
            foreach ($this->independentCacheList as $c) {
                $c->flush();
            }

            foreach ($this->cacheEvents as $e) {
                $e->flush();
            }
        }
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

    protected function getPagerInfo($nTotal, $nPerPage)
    {
        if ($nPerPage <= 0) {
            throw new \Exception('invalid value for number of items per page: ' . $nPerPage);
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

    protected function createIdentCode($uid)
    {
        $code = random_int(100000, 999999);

        // save in session
        $this->session->identCode = [
            'code'     => $code,
            'uid'      => $uid,
            'attempts' => 0,
            'expTime' => $this->request->timestamp + 600
        ];

        return $code;
    }

    protected function parseIdentCode($code)
    {
        if (!$this->session->identCode) {
            return null;
        }

        $idCode = $this->session->identCode;
        if ($idCode['attempts'] > 5 || $idCode['expTime'] < $this->request->timestamp) {
            // too many attempts, clear code
            $this->session->identCode = null;
            return null;
        }

        if ($code == $idCode['code']) {
            // valid code, clear code
            $this->session->identCode = null;
            return $idCode['uid'];
        } else {
            // attempts + 1
            $this->session->identCode['attempts'] = $idCode['attempts'] + 1;
            return null;
        }
    }

    protected function sendIdentCode($user)
    {
        // create user action and send out email
        $mailer = new Mailer('system');
        $mailer->to = $user->email;
        $siteName = ucfirst(self::$city->uriName) . 'BBS';
        $mailer->subject = $user->username . '在' . $siteName . '的用户安全验证码';
        $contents = [
            'username'    => $user->username,
            'ident_code' => $this->createIdentCode($user->id),
            'sitename'    => $siteName
        ];
        $mailer->body = new Template('mail/ident_code', $contents);

        return $mailer->send();
    }

    protected function handleSpammer(User $user)
    {
        $this->logger->info('SPAMMER FOUND: uid=' . $user->id);
        $user->delete();
        $u = new User();
        $u->lastAccessIP = inet_pton($this->request->ip);
        $users = $u->getList('createTime');
        $deleteAll = true;
        if (sizeof($users) > 1) {
            // check if we have old users that from this ip
            foreach ($users as $u) {
                if ($this->request->timestamp - $u['createTime'] > 2592000) {
                    $deleteAll = false;
                    break;
                }
            }

            if ($deleteAll) {
                $log = 'SPAMMER FROM IP ' . $this->request->ip . ': uid=';
                foreach ($users as $u) {
                    $spammer = new User($u['id'], null);
                    $spammer->delete();
                    $log = $log . $spammer->id . ' ';
                }
                $this->logger->info($log);
            }
        }

        if (false && $this->config->webmaster) { // turn off spammer email
            $mailer = new Mailer();
            $mailer->subject = 'SPAMMER detected and deleted (' . sizeof($users) . ($deleteAll ? ' deleted)' : ' not deleted)');
            $mailer->body = ' --node-- ' . $this->request->json['title'] . PHP_EOL . $this->request->json['body'];
            $mailer->to = $this->config->webmaster;
            $mailer->send();
        }
    }
}
