<?php declare(strict_types=1);

namespace site\handler\node\bookmark;

use lzx\exception\Forbidden;
use site\dbobject\User;
use site\handler\node\Node;

class Handler extends Node
{
    public function run(): void
    {
        if ($this->request->uid == self::UID_GUEST || !$this->args) {
            throw new Forbidden();
        }

        $nid = (int) $this->args[0];

        $u = new User($this->request->uid, 'id');

        $u->addBookmark($nid);
        $this->html = null;
    }
}
