<?php declare(strict_types=1);

namespace site\handler\api\user;

use site\Config;
use site\Service;
use site\dbobject\User;

class Handler extends Service
{
    /**
     * get user info
     * uri: /api/user/<uid>
     */
    public function get(): void
    {
        if (!$this->request->uid || empty($this->args) || !is_numeric($this->args[0])) {
            $this->forbidden();
        }

        $uid = (int) $this->args[0];
        $user = new User($uid, 'username,wechat,qq,website,sex,birthday,relationship,occupation,interests,favoriteQuotation,createTime,lastAccessTime,lastAccessIp,avatar,points,status');

        if ($user->status > 0) {
            $info = $user->toArray();
            unset($info['lastAccessIp']);
            $info['lastAccessCity'] = self::getLocationFromIP((string) $user->lastAccessIp);
            $info['topics'] = $user->getRecentNodes(self::$city->tidForum, 10);
            $info['comments'] = $user->getRecentComments(self::$city->tidForum, 10);

            $this->json($info);
        } else {
            $this->error('用户不存在');
        }
    }

    /**
     * update user info
     * As USER:
     * uri: /api/user/<uid>?action=put
     * post: <user properties>
     *
     * As GUEST:
     * uri: /api/user/<identCode>?action=put
     * post: password=<password>
     */
    public function put(): void
    {
        if (empty($this->args) || !is_numeric($this->args[0])) {
            $this->forbidden();
        }

        $uid = 0;
        if ($this->request->uid) {
            // user
            $uid = (int) $this->args[0];

            if ($uid != $this->request->uid) {
                $this->forbidden();
            }
        } else {
            // guest
            $uid = $this->parseIdentCode((int) $this->args[0]);
            if (!$uid) {
                $this->error('安全验证码错误，请检查使用邮件里的安全验证码');
            }
        }

        $u = new User($uid, 'id');

        if (array_key_exists('password', $this->request->post)) {
            if (array_key_exists('password_old', $this->request->post)) {
                // user to change password
                if (!$u->verifyPassword($this->request->post['password_old'])) {
                    $this->error('更改密码失败：输入的旧密码与当前密码不符，请确认输入正确的旧密码');
                }

                if ($this->request->post['password'] != $this->request->post['password2']) {
                    $this->error('更改密码失败：两次输入的新密码不一致');
                }

                unset($this->request->post['password_old']);
                unset($this->request->post['password2']);
            } else {
                // guest set new password
                $u->load('username,password,email');
                if (!$u->password) {
                    // this is a new user
                    if ($u->username == strstr($u->email, '@', true)) {
                        // load 3 users before this one
                        $userObj = new User();
                        $userObj->where('id', $uid - 4, '>');
                        $userObj->where('id', $uid, '<');
                        foreach ($userObj->getList('username,email,status') as $user) {
                            if ($user['username'] == strstr($user['email'], '@', true) && substr($u->username, 0, 4) == substr($user['username'], 0, 4)) {
                                // found username has the same prefix
                                // check location
                                $geo = geoip_record_by_name($this->request->ip);

                                if ((!$geo || $geo['region'] != 'TX') || strpos($this->request->post['password'], $u->username) !== false) {
                                    // non texas user, or username = password
                                    // treat as spammer, make as disabled
                                    $u->status = 0;
                                    // also disable spammer peer, if it is not disable
                                    if ($user['status'] > 0) {
                                        $up = new User();
                                        $up->id = $user['id'];
                                        $up->status = 0;
                                        $up->update();
                                    }
                                    // notify admin to check peers too
                                    $this->logger->error('Serial User found: username=' . $u->username . ' password=' . $this->request->post['password'] . ' (this user is deleted, but check on similar users)');
                                } else {
                                    // texas user, notify admin
                                    $this->logger->error('Serial User found: username=' . $u->username . ' password=' . $this->request->post['password']);
                                }
                                break;
                            }
                        }
                    }
                }
            }

            $this->request->post['password'] = User::hashPassword($this->request->post['password']);
        }

        if (array_key_exists('avatar', $this->request->post)) {
            $image = \base64_decode(substr($this->request->post['avatar'], strpos($this->request->post['avatar'], ',') + 1));
            if ($image !== false) {
                $config = Config::getInstance();
                $avatarFile = '/data/avatars/' . $this->request->uid . '_' . ($this->request->timestamp % 100) . '.png';
                file_put_contents($config->path['file'] . $avatarFile, $image);
                $this->request->post['avatar'] = $avatarFile;
            } else {
                unset($this->request->post['avatar']);
            }
        }

        foreach ($this->request->post as $k => $v) {
            $u->$k = $v;
        }

        $u->update();

        $this->json(null);

        $this->getIndependentCache('ap' . $u->id)->delete();
    }

    /**
     * uri: /api/user[?action=post]
     * post: username=<username>&email=<email>&captcha=<captcha>
     */
    public function post(): void
    {
        $this->validateCaptcha();

        // check username and email first
        if (empty($this->request->json['username'])) {
            $this->error('请填写用户名');
        } else {
            $username = strtolower($this->request->json['username']);
            if (strpos($username, 'admin') !== false || strpos($username, 'bbs') !== false) {
                $this->error('不合法的用户名，请选择其他用户名');
            }
        }

        if (!filter_var($this->request->json['email'], \FILTER_VALIDATE_EMAIL) || substr($this->request->json['email'], -8) == 'bccto.me') {
            $this->error('不合法的电子邮箱 : ' . $this->request->json['email']);
        }

        if (isset($this->request->json['submit']) || $this->isBot($this->request->json['email'])) {
            $this->logger->info('STOP SPAMBOT : ' . $this->request->json['email']);
            $this->error('系统检测到可能存在的注册机器人，所以不能提交您的注册申请。如果您使用的是QQ邮箱，请换用其他邮箱试试看。如果您认为这是一个错误的判断，请与网站管理员联系。');
        }

        $user = new User();
        $user->username = $this->request->json['username'];
        $user->password = null;
        $user->email = $this->request->json['email'];
        $user->lastAccessIp = inet_pton($this->request->ip);
        $user->cid = self::$city->id;
        $user->status = 1;

        // if user record exist, means this is a new-registered user, but need to re-send identification code
        $user->load('id');
        if (!$user->exists()) {
            $user->createTime = $this->request->timestamp;

            // spammer from Nanning
            $geo = geoip_record_by_name($this->request->ip);
            // from Nanning
            if ($geo && $geo['city'] === 'Nanning') {
                // mark as disabled
                $user->status = 0;
            }

            try {
                $user->add();
            } catch (\PDOException $e) {
                $this->error($e->errorInfo[2]);
            }
        }

        // create user action and send out email
        if ($this->sendIdentCode($user) === false) {
            $this->error('sending email error: ' . $user->email);
        } else {
            $this->json(null);
        }
    }

    /**
     * uri: /api/user/<uid>?action=delete
     */
    public function delete(): void
    {
        if ($this->request->uid != 1 || empty($this->args) || !is_numeric($this->args[0])) {
            $this->forbidden();
        }

        $uid = (int) $this->args[0];

        // not allowed to delete admin user
        if ($uid == 1) {
            $this->forbidden();
        }

        $this->deleteUser($uid);
        $this->json(null);
    }

    private function isBot(string $m): bool
    {
        $try1 = unserialize(self::curlGetData('http://www.stopforumspam.com/api?f=serial&email=' . $m));
        if ($try1['email']['appears'] == 1) {
            return true;
        }
        $try2 = self::curlGetData('http://botscout.com/test/?mail=' . $m);
        if ($try2[0] == 'Y') {
            return true;
        }
        return false;
    }
}
