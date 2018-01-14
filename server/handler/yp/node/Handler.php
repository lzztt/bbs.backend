<?php declare(strict_types=1);

namespace site\handler\yp\node;

use lzx\core\Mailer;
use lzx\html\Template;
use site\Controller;
use site\dbobject\Ad;
use site\dbobject\Comment;
use site\dbobject\Image;
use site\dbobject\Node;
use site\dbobject\NodeYellowPage;
use site\dbobject\Tag;

class Handler extends Controller
{
    public function run(): void
    {
        if ($this->request->uid != 1 && $this->request->uid != 8831 && $this->request->uid != 3) {
            $this->pageForbidden();
        }

        $tid = $this->args ? (int) $this->args[0] : 0;
        if ($tid <= 0) {
            $this->pageNotFound();
        }

        $tag = new Tag();
        $tag->parent = $tid;
        if ($tag->getCount() > 0) {
            $this->error('错误：您不能在该类别中添加黄页，请到它的子类别中添加。');
        }

        if (empty($this->request->post)) {
            $ad = new Ad();
            $ad->order('expTime', false);
            $this->var['content'] = new Template('editor_bbcode_yp', ['ads' => $ad->getList('name')]);
        } else {
            $node = new Node();
            $node->tid = $tid;
            $node->uid = $this->request->uid;
            $node->title = $this->request->post['title'];
            $node->createTime = $this->request->timestamp;
            $node->status = 1;
            $node->add();

            $comment = new Comment();
            $comment->nid = $node->id;
            $comment->tid = $tid;
            $comment->uid = $this->request->uid;
            $comment->body = $this->request->post['body'];
            $comment->createTime = $this->request->timestamp;
            $comment->add();

            $nodeYP = new NodeYellowPage();
            $nodeYP->nid = $node->id;
            $nodeYP->adId = (int) $this->request->post['aid'];
            foreach (array_diff($nodeYP->getProperties(), ['nid', 'adId']) as $k) {
                $nodeYP->$k = $this->request->post[$k] ? $this->request->post[$k] : null;
            }
            $nodeYP->add();

            if (isset($this->request->post['files'])) {
                $file = new Image();
                $file->cityId = self::$city->id;
                $file->updateFileList($this->request->post['files'], $this->config->path['file'], $node->id, $comment->id);
            }

            $tag = new Tag($tid, 'parent');

            foreach (['latestYellowPages', '/yp/' . $tid, '/yp/' . $tag->parent, '/'] as $key) {
                $this->getIndependentCache($key)->delete();
            }

            $this->sendConfirmationEmail(new Ad($nodeYP->adId), $node->id);

            $this->pageRedirect('/node/' . $node->id);
        }
    }

    private function sendConfirmationEmail(Ad $ad, int $nid): void
    {
        $mailer = new Mailer('ad');
        $mailer->to = $ad->email;
        $siteName = ucfirst(self::$city->uriName) . 'BBS';
        $mailer->subject = $ad->name . '在' . $siteName . '的电子黄页创建成功';
        $contents = [
            'name' => $ad->name,
            'url' => 'https://www.' . $this->config->domain . '/node/' . $nid,
            'sitename' => $siteName,
        ];
        $mailer->body = new Template('mail/adcreation', $contents);

        $mailer->bcc = $this->config->webmaster;
        $mailer->send();
    }
}
