<?php

declare(strict_types=1);

namespace site\handler\node\activity;

use lzx\core\Mailer;
use lzx\exception\ErrorMessage;
use lzx\exception\NotFound;
use lzx\html\HtmlElement;
use lzx\html\Template;
use site\dbobject\Activity;
use site\dbobject\Node as NodeObject;
use site\dbobject\Tag;
use site\gen\theme\roselife\ActivityCreate;
use site\handler\node\Node;

class Handler extends Node
{
    public function run(): void
    {
        $this->validateUser();

        list($nid, $type) = $this->getNodeType();
        switch ($type) {
            case self::FORUM_TOPIC:
                $this->activityForumTopic($nid);
                break;
        }
    }

    private function activityForumTopic(int $nid): void
    {
        $node = new NodeObject($nid, 'tid,uid,title');

        if (!$node->exists()) {
            throw new NotFound();
        }

        if ($node->tid != 16) {
            throw new ErrorMessage('错误：错误的讨论区。');
        }

        if ($this->user->id !== $node->uid && $this->user->id !== self::UID_ADMIN) {
            $this->logger->warning('wrong action : uid = ' . $this->user->id);
            throw new ErrorMessage('错误：您只能将自己发表的帖子发布为活动。');
        }

        if (!$this->request->data) {
            // display pm edit form
            $breadcrumb = ['首页' => '/'];
            $tag = new Tag($node->tid, 'name');
            $breadcrumb[$tag->name] = '/forum/' . $node->tid;
            $breadcrumb[$node->title] = null;

            $this->html->setContent(
                (new ActivityCreate())
                    ->setBreadcrumb(HtmlElement::breadcrumb($breadcrumb))
                    ->setExampleTime($this->request->timestamp - ($this->request->timestamp % 3600) + 259200)
            );
        } else {
            $startTime = strtotime($this->request->data['start_time']);
            $endTime = strtotime($this->request->data['end_time']);

            if ($startTime < $this->request->timestamp || $endTime < $this->request->timestamp) {
                throw new ErrorMessage('错误：活动开始时间或结束时间为过去的时间，不能发布为未来60天内的活动。');
                return;
            }

            if ($startTime > $this->request->timestamp + 5184000 || $endTime > $this->request->timestamp + 5184000) {
                throw new ErrorMessage('错误：活动开始时间或结束时间太久远，不能发布为未来60天内的活动。');
                return;
            }

            if ($startTime > $endTime) {
                throw new ErrorMessage('错误：活动结束时间在开始时间之前，请重新填写时间。');
                return;
            }

            if ($endTime - $startTime > 86400) { // 1 day
                $mailer = new Mailer();
                $mailer->setTo('admin@' . $this->config->domain);
                $mailer->setSubject('新活动 ' . $nid . ' 长于一天 (请检查)');
                $mailer->setBody($node->title . ' : ' . date('m/d/Y H:i', $startTime) . ' - ' . date('m/d/Y H:i', $endTime));
                if ($mailer->send() === false) {
                    $this->logger->info('sending long activity notice email error.');
                }
            }

            $activity = new Activity();
            $activity->addActivity($nid, $startTime, $endTime);

            $this->getIndependentCache('recentActivities')->delete();

            $this->html->setContent(Template::fromStr('您的活动申请已经提交并等待管理员激活，一般会在一小时之内被激活并且提交到首页，活动被激活后您将会收到电子邮件通知。'));
        }
    }
}
