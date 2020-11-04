<?php

declare(strict_types=1);

namespace site\handler\api\identificationcode;

use lzx\exception\ErrorMessage;
use site\Service;
use site\dbobject\User;

class Handler extends Service
{
    /**
     * uri: /api/identificationcode[?action=post]
     * post: username=<username>&email=<email>&&captcha=<captcha>
     */
    public function post(): void
    {
        $this->validateCaptcha();

        if (!$this->request->data['username']) {
            throw new ErrorMessage('请输入用户名');
        }

        if (!$this->request->data['email']) {
            throw new ErrorMessage('请输入注册电子邮箱');
        }

        $user = new User();
        $user->username = $this->request->data['username'];
        $user->load('email');

        if ($user->exists()) {
            if ($user->email != $this->request->data['email']) {
                throw new ErrorMessage('您输入的电子邮箱与与此用户的注册邮箱不匹配，请检查是否输入了正确的注册邮箱');
            }

            // create user action and send out email
            if ($this->sendIdentCode($user) === false) {
                throw new ErrorMessage('sending email error: ' . $user->email);
            } else {
                $this->json();
            }
        } else {
            throw new ErrorMessage('你输入的用户名不存在');
        }
    }
}
