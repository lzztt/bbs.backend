<?php declare(strict_types=1);

namespace site\handler\unsubscribe;

use lzx\html\Template;
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
                $this->html->setContent(Template::fromStr('<br><br>You have been unsubscribed.<br><br>'));
            } else {
                $user = new User($uid, 'id');
                $user->type = 1;
                $user->update('type');
                $this->html->setContent(Template::fromStr('<br><br>' . $email . ' has been unsubscribed.<br><br>'));
            }
        }
    }
}
