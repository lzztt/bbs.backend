<?php declare(strict_types=1);

namespace site;

use lzx\core\Handler;
use lzx\core\Logger;
use lzx\core\Mailer;
use lzx\core\Request;
use lzx\core\Response;
use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use lzx\exception\NotFound;
use lzx\html\Template;
use site\Config;
use site\HandlerTrait;
use site\Session;
use site\dbobject\User;

abstract class Service extends Handler
{
    use HandlerTrait;

    const UID_GUEST = 0;
    const UID_ADMIN = 1;
    const ACTIONS = ['get', 'post', 'put', 'delete'];

    public $action;
    public $args;
    public $session;

    public function __construct(Request $req, Response $response, Config $config, Logger $logger, Session $session, array $args)
    {
        parent::__construct($req, $response, $logger);
        $this->response->type = Response::JSON;
        $this->session = $session;
        $this->config = $config;
        $this->args = $args;
        $this->staticInit();
    }

    public function run(): void
    {
        if (array_key_exists('action', $this->request->get)
                && in_array($this->request->get['action'], self::ACTIONS)) {
            $action = $this->request->get['action'];
        } else {
            $action = ($this->request->post || $this->request->json) ? 'post' : 'get';
        }
        if (!method_exists($this, $action)) {
            throw new NotFound();
        }
        $this->$action();
    }

    protected function validateUser(): void
    {
        if ($this->request->uid === self::UID_GUEST) {
            throw new ErrorMessage('请先登陆');
        }
    }

    protected function validateAdmin(): void
    {
        if ($this->request->uid !== self::UID_ADMIN) {
            throw new Forbidden();
        }
    }

    protected function validateCaptcha(): void
    {
        $captcha = $this->request->post['captcha']
                ? $this->request->post['captcha']
                : $this->request->json['captcha'];
        if (!$captcha || !$this->session->captcha
                || strtolower($captcha) !== strtolower($this->session->captcha)) {
            throw new ErrorMessage('图形验证码错误');
        }
        unset($this->session->captcha);
    }

    protected function createIdentCode(int $uid): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->session->identCode = [
            'code' => $code,
            'uid' => $uid,
            'attempts' => 0,
            'expTime' => $this->request->timestamp + 600
        ];
        return $code;
    }

    protected function parseIdentCode(string $code): int
    {
        if (!$this->session->identCode) {
            return self::UID_GUEST;
        }

        $c = $this->session->identCode;
        if ($c['attempts'] > 3 || $c['expTime'] < $this->request->timestamp) {
            $this->session->identCode = null;
            return self::UID_GUEST;
        }

        if ($code === $c['code']) {
            $this->session->identCode = null;
            return $c['uid'];
        }

        $this->session->identCode['attempts'] = $c['attempts'] + 1;
        return self::UID_GUEST;
    }

    protected function sendIdentCode(User $user): bool
    {
        $mailer = new Mailer('system');
        $mailer->setTo($user->email);
        $siteName = ucfirst(self::$city->uriName) . 'BBS';
        $mailer->setSubject($user->username . '在' . $siteName . '的用户安全验证码');
        $contents = [
            'username'    => $user->username,
            'ident_code' => $this->createIdentCode($user->id),
            'sitename'    => $siteName
        ];
        $mailer->setBody((string) new Template('mail/ident_code', $contents));

        return $mailer->send();
    }
}
