<?php declare(strict_types=1);

namespace site\handler\node\bookmark;

use site\handler\node\Node;
use site\dbobject\User;

class Handler extends Node
{
    public function run()
    {
        if ($this->request->uid == self::UID_GUEST || !$this->args) {
            $this->pageForbidden();
        }

        $nid = (int) $this->args[0];

        $u = new User($this->request->uid, null);

        $u->addBookmark($nid);
        $this->html = null;
    }
}
