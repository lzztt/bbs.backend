<?php declare(strict_types=1);

namespace site;

use Exception;
use lzx\core\Service as LzxService;
use lzx\core\Request;
use lzx\core\Response;
use lzx\core\Logger;
use lzx\core\Mailer;
use lzx\html\Template;
use site\Session;
use lzx\cache\CacheHandler;
use site\Config;
use site\dbobject\City;

// handle RESTful web API
// resource uri: /api/<resource>&action=[get,post,put,delete]

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

    // default RESTful get/post/put/delete
    public function __call($name, $args)
    {
        $this->forbidden();
    }

    protected function validateCaptcha()
    {
        if (!$this->request->json['captcha'] || !$this->session->captcha
                || strtolower($this->request->json['captcha']) !== strtolower($this->session->captcha)) {
            $this->error('图形验证码错误');
        }
        unset($this->session->captcha);
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

    protected function createIdentCode($uid)
    {
        $code = rand(100000, 999999);

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
}
