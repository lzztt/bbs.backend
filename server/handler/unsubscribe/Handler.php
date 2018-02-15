<?php declare(strict_types = 1);

namespace site\handler\unsubscribe;

use site\Controller;
use site\dbobject\User;

class Handler extends Controller
{
    public function run(): void
    {
        $code = $this->request->data['c'];
        if ($code) {
            list($email, $uid) = User::decodeEmail($code);
            if ($uid === self::UID_GUEST) {
                $this->var['content'] = '<br><br>You have been unsubscribed.<br><br>';
            } else {
                $user = new User($uid, 'id');
                $user->type = 1;
                $user->update('type');
                $this->var['content'] = '<br><br>' . $email . ' has been unsubscribed.<br><br>';
            }
        }
    }
}
