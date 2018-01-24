<?php declare(strict_types=1);

namespace site\handler\api\authentication;

use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use site\Service;
use site\dbobject\User;

class Handler extends Service
{
    // check if a user is logged in
    // uri: /api/authentication/<session_id>
    // return: uid
    public function get(): void
    {
        if (empty($this->args) || $this->args[0] != $this->session->getSessionID()) {
            $this->json(['sessionID' => $this->session->getSessionID(), 'uid' => 0]);
            return;
        }

        if ($this->request->uid) {
            $user = new User($this->request->uid, 'username');
            $this->json(['sessionID' => $this->session->getSessionID(), 'uid' => $user->id, 'username' => $user->username, 'role' => $user->getUserGroup()]);
        } else {
            $this->json(['sessionID' => $this->session->getSessionID(), 'uid' => 0]);
        }
    }

    // login a user
    // uri: /api/authentication[?action=post]
    // post: username=<username>&password=<password>
    // post: email=<email>&password=<password>
    // return: session id and uid
    public function post(): void
    {
        if (isset($this->request->post['password']) && isset($this->request->post['email'])) {
            // todo: login times control
            $user = new User();
            $loggedIn = $user->loginWithEmail($this->request->post['email'], $this->request->post['password']);

            if ($loggedIn) {
                $this->session->setUserID($user->id);
                $this->json(['sessionID' => $this->session->getSessionID(), 'uid' => $user->id, 'username' => $user->username, 'role' => $user->getUserGroup()]);
                return;
            } else {
                $this->logger->info('Login Fail: ' . $user->email . ' | ' . $this->request->ip);
                if ($user->exists()) {
                    if (empty($user->password)) {
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
        if (empty($this->args) || $this->args[0] != $this->session->getSessionID()) {
            throw new Forbidden();
        }

        $this->session->clear(); // keep session record but clear session data

        $this->json(null);
    }
}
