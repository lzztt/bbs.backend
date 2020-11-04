<?php

declare(strict_types=1);

namespace site;

use lzx\core\Logger;
use lzx\core\Mailer;
use lzx\core\Request;
use lzx\core\Response;
use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use lzx\exception\NotFound;
use site\Config;
use site\Handler;
use site\Session;
use site\dbobject\User;
use site\gen\theme\roselife\mail\IdentCode;

abstract class Service extends Handler
{
    const METHODS = ['get', 'post', 'put', 'delete'];

    public function __construct(Request $req, Response $response, Config $config, Logger $logger, Session $session, array $args)
    {
        parent::__construct($req, $response, $config, $logger, $session, $args);
        $this->response->type = Response::JSON;
    }

    public function run(): void
    {
        $method = strtolower($this->request->method);

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
        $mailer->setBody(
            (string) (new IdentCode())
                ->setUsername($user->username)
                ->setIdentCode($this->createIdentCode($user->id))
                ->setSitename($siteName)
        );

        return $mailer->send();
    }
}
