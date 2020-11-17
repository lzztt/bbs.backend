<?php

declare(strict_types=1);

namespace site\handler\api\authentication;

use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use site\Service;
use site\dbobject\SessionEvent;
use site\dbobject\User;

class Handler extends Service
{
    // check if a user is logged in
    // uri: /api/authentication/<session_id>
    // return: uid
    public function get(): void
    {
        $return = [
            'sessionID' => $this->session->id(),
            'uid' => self::UID_GUEST,
            'username' => null,
            'role' => null
        ];
        if ($this->args && $this->args[0] === $this->session->id() && $this->request->uid !== self::UID_GUEST) {
            $user = new User($this->request->uid, 'username, status');
            if ($user->exists() && $user->status > 0) {
                $return['uid'] = $user->id;
                $return['username'] = $user->username;
                $return['role'] = $user->getUserGroup();
            }
        }
        $this->json($return);
    }

    // login a user
    // uri: /api/authentication[?action=post]
    // post: email=<email>&password=<password>
    // return: session id and uid
    public function post(): void
    {
        if (isset($this->request->data['password']) && isset($this->request->data['email'])) {
            // todo: login times control
            $user = new User();
            $loggedIn = $user->loginWithEmail($this->request->data['email'], $this->request->data['password']);

            if ($loggedIn) {
                $this->session->regenerateId();
                $this->session->set('uid', $user->id);
                $this->updateSessionEvent(SessionEvent::EVENT_BEGIN);

                $this->json(['sessionID' => $this->session->id(), 'uid' => $user->id, 'username' => $user->username, 'role' => $user->getUserGroup()]);
                return;
            } else {
                $this->logger->info('Login Fail: ' . $user->email . ' | ' . $this->request->ip);
                if ($user->exists()) {
                    if (!$user->password) {
                        throw new ErrorMessage('用户帐号尚未激活，请使用注册email里的安全验证码来设置初始密码。如有问题请联络网站管理员。');
                    }

                    if ($user->status == 1) {
                        throw new ErrorMessage('错误的密码。');
                    } else {
                        throw new ErrorMessage('用户帐号已被封禁，如有问题请联络网站管理员。');
                    }
                } else {
                    throw new ErrorMessage('用户不存在。');
                }
            }
        } else {
            throw new ErrorMessage('请填写邮箱名和密码。');
        }
    }

    // logout a user
    // uri: /api/authentication/<session_id>?action=delete
    public function delete(): void
    {
        if (!$this->args || $this->args[0] != $this->session->id()) {
            throw new Forbidden();
        }

        $this->updateSessionEvent(SessionEvent::EVENT_END);

        $this->session->clear(); // keep session record but clear session data

        $this->json();
    }
}
