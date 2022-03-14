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
use site\gen\theme\roselife\mail\IdentCode;

abstract class Service extends Handler
{
    const LIMIT_ROBOT = 0;
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
        if ($this->user->id !== self::UID_ADMIN) {
            throw new Forbidden();
        }
    }

    protected function createIdentCode(string $email): string
    {
        $code = random_int(0, 999999);
        $this->session->set('identCode', [
            'code' => $code,
            'email' => $email,
            'attempts' => 0,
            'expTime' => $this->request->timestamp + 900
        ]);
        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }

    protected function parseIdentCode(string $code): string
    {
        $c = $this->session->get('identCode');
        if (!$c) {
            return '';
        }

        if ($c['attempts'] > 2 || $c['expTime'] < $this->request->timestamp) {
            $this->session->set('identCode', null);
            return '';
        }

        if ((int) str_replace(' ', '', $code) === $c['code']) {
            $this->session->set('identCode', null);
            return $c['email'];
        }

        $c['attempts'] += 1;
        $this->session->set('identCode', $c);
        return '';
    }

    protected function sendIdentCode(string $email): bool
    {
        $mailer = new Mailer('system');
        $mailer->setTo($email);
        $siteName = $this->getSiteName();
        $mailer->setSubject('您在' . $siteName . '的安全验证码');
        $mailer->setBody(
            (string) (new IdentCode())
                ->setIdentCode($this->createIdentCode($email))
        );

        return $mailer->send();
    }
}
