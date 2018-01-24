<?php declare(strict_types=1);

namespace site\handler\node\tag;

use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use lzx\exception\Redirect;
use site\dbobject\Node as NodeObject;
use site\handler\node\Node;

class Handler extends Node
{
    public function run(): void
    {
        list($nid, $type) = $this->getNodeType();
        $uri = $type === self::FORUM_TOPIC ? '/forum/' : '/yp/';

        if (empty($this->args)) {
            throw new ErrorMessage('no tag id specified');
        }

        $newTagID = (int) $this->args[0];

        $nodeObj = new NodeObject($nid, 'uid,tid');
        if ($this->request->uid == 1 || $this->request->uid == $nodeObj->uid) {
            $oldTagID = $nodeObj->tid;
            $nodeObj->tid = $newTagID;
            $nodeObj->update('tid');

            foreach ([$uri . $oldTagID, $uri . $newTagID, '/node/' . $nid] as $key) {
                $this->getIndependentCache($key)->delete();
            }

            throw new Redirect('/node/' . $nid);
        } else {
            $this->logger->warn('wrong action : uid = ' . $this->request->uid);
            throw new Forbidden();
        }
    }
}
