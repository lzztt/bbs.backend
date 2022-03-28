<?php

declare(strict_types=1);

namespace site\handler\yp\node;

use lzx\core\Mailer;
use lzx\core\Response;
use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use lzx\exception\NotFound;
use lzx\exception\Redirect;
use site\Controller;
use site\dbobject\Ad;
use site\dbobject\Comment;
use site\dbobject\Image;
use site\dbobject\Node;
use site\dbobject\NodeYellowPage;
use site\dbobject\Tag;
use site\gen\theme\roselife\EditorBbcodeYp;
use site\gen\theme\roselife\mail\Adcreation;

class Handler extends Controller
{
    public function run(): void
    {
        if ($this->user->id !== self::UID_ADMIN) {
            throw new Forbidden();
        }

        $tid = $this->args ? (int) $this->args[0] : 0;
        if ($tid <= 0) {
            throw new NotFound();
        }

        $tag = new Tag();
        $tag->parent = $tid;
        if ($tag->getCount() > 0) {
            throw new ErrorMessage('错误：您不能在该类别中添加黄页，请到它的子类别中添加。');
        }

        if (!$this->request->data) {
            $ad = new Ad();
            $ad->order('expTime', false);
            $this->html->setContent(
                (new EditorBbcodeYp())
                    ->setAds($ad->getList('name'))
            );
        } else {
            $this->response->type = Response::JSON;

            $node = new Node();
            $node->tid = $tid;
            $node->uid = $this->user->id;
            $node->title = $this->request->data['title'];
            $node->createTime = $this->request->timestamp;
            $node->status = 1;
            $node->add();

            $comment = new Comment();
            $comment->nid = $node->id;
            $comment->tid = $tid;
            $comment->uid = $this->user->id;
            $comment->body = $this->request->data['body'];
            $comment->createTime = $this->request->timestamp;
            $comment->add();

            $nodeYP = new NodeYellowPage();
            $nodeYP->nid = $node->id;
            $nodeYP->adId = (int) $this->request->data['aid'];
            foreach (array_diff($nodeYP->getProperties(), ['nid', 'adId']) as $k) {
                $nodeYP->$k = $this->request->data[$k] ? $this->request->data[$k] : null;
            }
            $nodeYP->add();

            $files = $this->getFormFiles();

            if ($files) {
                $file = new Image();
                $file->cityId = self::$city->id;
                $file->updateFileList($files, $this->config->path['file'], $node->id, $comment->id);
            }

            $tag = new Tag($tid, 'parent');

            $this->getCacheEvent('YellowPageUpdate')->trigger();
            $this->getIndependentCache('/yp/' . $tid)->delete();

            $this->sendConfirmationEmail(new Ad($nodeYP->adId), $node->id);

            throw new Redirect('/node/' . $node->id);
        }
    }

    private function sendConfirmationEmail(Ad $ad, int $nid): void
    {
        $mailer = new Mailer('ad');
        $mailer->setTo($ad->email);
        $siteName = $this->getSiteName();
        $mailer->setSubject($ad->name . '在' . $siteName . '的电子黄页创建成功');
        $mailer->setBody(
            (string) (new Adcreation())
                ->setName($ad->name)
                ->setUrl('https://www.' . $this->config->domain . '/node/' . $nid)
                ->setSitename($siteName)
        );

        $mailer->setBcc($this->config->webmaster);
        $mailer->send();
    }
}
