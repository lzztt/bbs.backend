<?php

namespace site\handler\node\bookmark;

use site\handler\node\Node;
use site\dbobject\Node as NodeObject;
use site\dbobject\User;

class Handler extends Node
{
    public function run()
    {
        if ($this->request->uid == self::UID_GUEST || !$this->id) {
            $this->pageForbidden();
        }

        $nid = $this->id;

        $u = new User($this->request->uid, null);

        $u->addBookmark($nid);
        $this->html = null;
    }
}
