<?php

declare(strict_types=1);

namespace site\handler\api\identificationcode;

use lzx\db\MemStore;
use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use site\Service;

class Handler extends Service
{
    /**
     * uri: /api/identificationcode
     * post: email=<email>
     */
    public function post(): void
    {
        if ($this->request->isRobot()) {
            throw new Forbidden();
        }

        if (empty($this->request->data['email'])) {
            throw new ErrorMessage('请输入电子邮箱。');
        }

        $email = $this->request->data['email'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || substr($email, -8) == 'bccto.me') {
            throw new ErrorMessage('不合法的电子邮箱：' . $email);
        }

        // login rate control
        $rateLimiter = MemStore::getRedis(MemStore::RATE);
        if (!$rateLimiter->set('login:' . $this->request->ip, '', ['nx', 'ex' => 60])) {
            throw new ErrorMessage('登录系统忙，请稍后再试。');
        }

        if ($this->isBot($this->request->ip, $email)) {
            $this->logger->info('STOP SPAMBOT : ' . $this->request->ip . ' ' . $email);
            throw new ErrorMessage('系统检测到可能存在的注册机器人。如果您使用的是QQ邮箱，请换用其他邮箱试试看。如果您认为这是一个错误的判断，请与网站管理员联系。');
        }

        if ($this->sendIdentCode($email) === false) {
            throw new ErrorMessage('发送邮件错误：' . $email);
        } else {
            $this->json();
        }
    }

    private function isBot(string $ip, string $email): bool
    {
        // TODO: check ip against current users and spammers, block if only spammers from the ip
        $url = 'http://api.stopforumspam.org/api?json&ip=' . $ip . '&email=' . $email;
        $data = json_decode(self::curlGet($url));
        if ((!empty($data->ip->appears) && $data->ip->appears > 0)
            || (!empty($data->email->appears) && $data->email->appears > 0)
        ) {
            return true;
        }
        return false;
    }
}
