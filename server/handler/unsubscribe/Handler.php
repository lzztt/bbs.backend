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
            $this->var['content'] = $email . ' has been unsubscribed.';
            return;
        }
    }
}
