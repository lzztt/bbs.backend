<?php declare(strict_types=1);

namespace site;

use lzx\core\Service as LzxService;
use lzx\core\Request;
use lzx\core\Response;
use lzx\core\Logger;
use lzx\core\Mailer;
use lzx\html\Template;
use site\Session;
use site\Config;
use site\HandlerTrait;

// handle RESTful web API
// resource uri: /api/<resource>&action=[get,post,put,delete]

abstract class Service extends LzxService
{
    use HandlerTrait;

    const UID_GUEST = 0;
    const UID_ADMIN = 1;

    private static $actions = ['get', 'post', 'put', 'delete'];
    public $action;
    public $args;
    public $session;

    public function __construct(Request $req, Response $response, Config $config, Logger $logger, Session $session)
    {
        parent::__construct($req, $response, $logger);
        $this->session = $session;
        $this->config = $config;
        $this->staticInit();
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
                || strtolower((string) $this->request->json['captcha']) !== strtolower($this->session->captcha)) {
            $this->error('图形验证码错误');
        }
        unset($this->session->captcha);
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
