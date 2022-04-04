<?php

declare(strict_types=1);

namespace site\handler\api\user;

use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use site\Config;
use site\dbobject\SessionEvent;
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
        $this->validateUser();
        if (!$this->args || !is_numeric($this->args[0])) {
            throw new Forbidden();
        }

        $uid = (int) $this->args[0];
        $user = new User($uid, $uid === $this->user->id
            ? 'username,about,createTime,avatar,status,reputation,contribution'
            : 'username,about,createTime,avatar,status,reputation');

        $se = new SessionEvent();
        $se->userId = $uid;
        $se->load('time,ip');
        if ($user->status > 0) {
            $info = $user->toArray();
            $info['lastAccessTime'] = $se->time;
            $info['lastAccessCity'] = self::getLocationFromIp((string) $se->ip);
            $info['topics'] = $user->getRecentNodes(self::$city->tidForum, 10);
            $info['comments'] = $user->getRecentComments(self::$city->tidForum, 10);

            $this->json($info);
        } else {
            throw new ErrorMessage('用户不存在');
        }
    }

    /**
     * update user info
     * As USER:
     * uri: /api/user/<uid>
     * post: <user properties>
     */
    public function put(): void
    {
        $this->validateUser(checkUsername: !array_key_exists('username', $this->request->data));
        if (!$this->args || !is_numeric($this->args[0])) {
            throw new Forbidden();
        }

        $uid = (int) $this->args[0];
        if ($uid !== $this->user->id) {
            throw new Forbidden();
        }

        if (array_key_exists('username', $this->request->data)) {
            $username = strtolower($this->request->data['username']);
            if (empty($username)) {
                throw new ErrorMessage('不能使用此用户名，请选用其他用户名。');
            }
            $denylist = [
                'admin',
                array_shift(explode('.', self::$city->domain))
            ];
            foreach ($denylist as $w) {
                if (strpos($username, $w) !== false) {
                    throw new ErrorMessage('不能使用此用户名，请选用其他用户名。');
                }
            }

            $u = new User();
            $u->username = $username;
            $u->load('id');
            if ($u->exists()) {
                throw new ErrorMessage('此用户名已被使用，请选用其他用户名。');
            }
        }

        $u = new User($uid, 'id');

        if (array_key_exists('avatar', $this->request->data)) {
            $image = base64_decode(substr($this->request->data['avatar'], strpos($this->request->data['avatar'], ',') + 1));
            if ($image !== false) {
                $config = Config::getInstance();
                $avatarFile = '/data/avatars/' . $this->user->id . '_' . ($this->request->timestamp % 100) . '.png';
                file_put_contents($config->path['file'] . $avatarFile, $image);
                $this->request->data['avatar'] = $avatarFile;
            } else {
                unset($this->request->data['avatar']);
            }
        }

        foreach ($this->request->data as $k => $v) {
            $u->$k = $v;
        }

        $u->update();

        $this->json();

        $this->getIndependentCache('ap' . $u->id)->delete();
    }

    /**
     * uri: /api/user/<uid>
     */
    public function delete(): void
    {
        $this->validateAdmin();
        if (!$this->args || !is_numeric($this->args[0])) {
            throw new Forbidden();
        }

        $uid = (int) $this->args[0];

        // not allowed to delete admin user
        if ($uid === 1) {
            throw new Forbidden();
        }

        $this->deleteUser($uid);

        $this->json();
    }
}
