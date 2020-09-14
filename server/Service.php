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
    const METHODS = ['get', 'post', 'put', 'delete'];

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
        $method = $this->request->method;
        if (array_key_exists('action', $this->request->data)) {
            $method = $this->request->data['action'];
            unset($this->request->data['action']);
        }

        if (!in_array($method, self::METHODS) || !method_exists($this, $method)) {
            throw new NotFound();
        }
        $this->$method();
    }

    protected function validateAdmin(): void
    {
        if ($this->request->uid !== self::UID_ADMIN) {
            throw new Forbidden();
        }
    }

    protected function validateCaptcha(): void
    {
        $input = $this->request->data['captcha'];
        $captcha = $this->session->get('captcha');
        if (!$input || !$captcha || strtolower($input) !== strtolower($captcha)) {
            throw new ErrorMessage('图形验证码错误');
        }
        $this->session->set('captcha', null);
    }

    protected function createIdentCode(int $uid): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->session->set('identCode', [
            'code' => $code,
            'uid' => $uid,
            'attempts' => 0,
            'expTime' => $this->request->timestamp + 3600
        ]);
        return $code;
    }

    protected function parseIdentCode(string $code): int
    {
        $c = $this->session->get('identCode');
        if (!$c) {
            return self::UID_GUEST;
        }

        if ($c['attempts'] > 2 || $c['expTime'] < $this->request->timestamp) {
            $this->session->set('identCode', null);
            return self::UID_GUEST;
        }

        if ($code === $c['code']) {
            $this->session->set('identCode', null);
            return $c['uid'];
        }

        $c['attempts'] += 1;
        $this->session->set('identCode', $c);
        return self::UID_GUEST;
    }

    protected function sendIdentCode(User $user): bool
    {
        $mailer = new Mailer('system');
        $mailer->setTo($user->email);
        $siteName = $this->getSiteName();
        $mailer->setSubject($user->username . '在' . $siteName . '的用户安全验证码');
        $contents = [
            'username' => $user->username,
            'ident_code' => $this->createIdentCode($user->id),
            'sitename' => $siteName
        ];
        $mailer->setBody((string) new Template('mail/ident_code', $contents));

        return $mailer->send();
    }
}
