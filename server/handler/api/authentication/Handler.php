<?php

declare(strict_types=1);

namespace site\handler\api\authentication;

use Exception;
use lzx\db\MemStore;
use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use site\Service;
use site\dbobject\User;

class Handler extends Service
{
    // check if a user is logged in
    // uri: /api/authentication
    // return: uid
    public function get(): void
    {
        try {
            $this->validateUser();
            $this->json([
                'sessionID' => $this->session->id(),
                'uid' => $this->user->id,
                'username' => $this->user->username,
                'role' => $this->user->getUserGroup()
            ]);
        } catch (Exception $e) {
            $this->json([
                'sessionID' => $this->session->id(),
                'uid' => self::UID_GUEST,
                'username' => null,
                'role' => null
            ]);
        }
    }

    // login a user
    // uri: /api/authentication[?action=post]
    // post: email=<email>&password=<password>
    // return: session id and uid
    public function post(): void
    {
        if (isset($this->request->data['password']) && isset($this->request->data['email'])) {
            // login rate control
            $rateLimiter = MemStore::getRedis(MemStore::RATE);
            $key = 'login:' . $this->request->ip . ':' . $this->request->data['email'];
            $count = $rateLimiter->incr($key);
            if ($count > 5) {
                throw new ErrorMessage('登录次数太多，请稍后再试。');
            }
            $rateLimiter->expire($key, 300);

            $user = new User();
            $user->email = $this->request->data['email'];
            $user->load();

            if (!$user->exists()) {
                throw new ErrorMessage('错误的邮箱或密码。');
            }

            if (!$user->password) {
                throw new ErrorMessage('帐号尚未激活，请使用注册email里的安全验证码来设置初始密码。');
            }

            if ($user->status !== 1) {
                throw new ErrorMessage('帐号已被封禁。');
            }

            if ($user->lockedUntil > $this->request->timestamp) {
                throw new ErrorMessage('帐号被暂时封禁至' . date('Y-m-d', $user->lockedUntil) . '，请稍后再尝试登陆。');
            }

            if ($user->reputation < 0 && $user->contribution < 0) {
                throw new ErrorMessage('用户的社区声望和贡献不足，不能登陆。');
            }

            if ($user->verifyPassword($this->request->data['password'])) {
                $this->session->set('uid', $user->id);
                $this->session->regenerateId();

                $this->json([
                    'sessionID' => $this->session->id(),
                    'uid' => $user->id,
                    'username' => $user->username,
                    'role' => $user->getUserGroup()
                ]);
                return;
            } else {
                $this->logger->info('Login Fail: ' . $user->email . ' | ' . $this->request->ip);
                throw new ErrorMessage('错误的邮箱或密码。');
            }
        } else {
            throw new ErrorMessage('请填写邮箱和密码。');
        }
    }

    // logout a user
    // uri: /api/authentication/<session_id>?action=delete
    public function delete(): void
    {
        if (!$this->args || $this->args[0] != $this->session->id()) {
            throw new Forbidden();
        }

        $this->session->clear(); // keep session record but clear session data

        $this->json();
    }
}
