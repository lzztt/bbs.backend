<?php

declare(strict_types=1);

namespace site\handler\api\message;

use Exception;
use lzx\core\Mailer;
use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use site\Service;
use site\dbobject\PrivMsg;
use site\dbobject\User;

class Handler extends Service
{
    const LIMIT_GUEST = 0;
    const LIMIT_USER = 1000;

    const TOPICS_PER_PAGE = 25;

    private static $mailbox = ['inbox', 'sent'];

    /**
     * get private messages in user's mailbox (inbox,sent)
     * uri: /api/message/<mailbox>
     *      /api/message/<mailbox>?p=<pageNo>
     *
     * get private message
     * uri: /api/message/<mid>
     *
     * get new message count
     * uri: /api/message/new
     */
    public function get(): void
    {
        $this->validateUser();
        if (!$this->args) {
            throw new Forbidden();
        }

        if (is_numeric($this->args[0])) {
            $return = $this->getMessage((int) $this->args[0]);
        } else {
            if ($this->args[0] == 'new') {
                $return = $this->getNewMessageCount();
            } else {
                $return = $this->getMessageList($this->args[0]);
            }
        }

        $this->json($return);
    }

    /**
     * send a private message to user
     * uri: /api/message[?action=post]
     * post: toUid=<toUid>&body=<body>(&topicMid=<topicMid>)
     * return: new created message
     */
    public function post(): void
    {
        $this->validateUser();

        $topicMid = null;
        if (array_key_exists('topicMid', $this->request->data)) {
            $topicMid = (int) $this->request->data['topicMid'];
            if ($topicMid <= 0) {
                $topicMid = null;
            }
        }
        try {
            $this->validatePost();
            $this->dedup();
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage(), ['post' => $this->request->data]);
            throw new ErrorMessage($e->getMessage());
        }
        $pm = new PrivMsg();

        // validate toUid
        $toUid = (int) $this->request->data['toUid'];
        if ($toUid) {
            if ($toUid === $this->user->id) {
                throw new ErrorMessage('不能给自己发送站内短信');
            }

            if ($topicMid) {
                // reply an existing message topic
                $toUser = $pm->getReplyTo($topicMid, $this->user->id);

                if (!$toUser) {
                    throw new ErrorMessage('短信不存在，未找到短信收信人');
                }

                if ($toUser['id'] != $toUid) {
                    throw new ErrorMessage('收件人帐号不匹配，无法发送短信');
                }
            }
        } else {
            throw new ErrorMessage('未指定短信收件人，无法发送短信');
        }

        $user = new User($toUid, 'username,email');

        if (!$user->exists()) {
            throw new ErrorMessage('收信人用户不存在');
        }

        // save pm to database
        if (strlen($this->request->data['body']) < 5) {
            throw new ErrorMessage('短信正文需最少5个字母或3个汉字');
        }

        $pm->fromUid = $this->user->id;
        $pm->toUid = $user->id;
        $pm->body = $this->request->data['body'];
        $pm->time = $this->request->timestamp;
        if ($topicMid) {
            // reply an existing message topic
            $pm->msgId = $topicMid;
            $pm->add();
        } else {
            // start a new message topic
            $pm->add();
            $pm->msgId = $pm->id;
            $pm->update('msgId');
        }

        if ($user->email) {
            $mailer = new Mailer('pm');
            $mailer->setTo($user->email);
            $mailer->setSubject($user->username . ' 您有一封新的站内短信');
            $mailer->setBody($user->username . ' 您有一封新的站内短信' . "\n" . '请登录后点击下面链接阅读' . "\n" . 'https://' . $this->request->domain . '/user/pm/' . $pm->msgId);
            if (!$mailer->send()) {
                $this->logger->error('PM EMAIL REMINDER SENDING ERROR: ' . $pm->id);
            }
        }

        $sender = $this->user;
        $this->json([
            'id' => $pm->id,
            'mid' => $pm->msgId,
            'time' => $pm->time,
            'body' => $pm->body,
            'uid' => $pm->fromUid,
            'username' => $sender->username,
            'avatar' => $sender->avatar
        ]);
    }

    /**
     * delete a private message from user's message box
     * uri: /api/message/<mid>(,<mid>,...)?action=delete
     */
    public function delete(): void
    {
        $this->validateUser();
        if (!$this->args) {
            throw new Forbidden();
        }

        $mids = [];

        foreach (explode(',', $this->args[0]) as $mid) {
            if (is_numeric($mid) && intval($mid) > 0) {
                $mids[] = (int) $mid;
            }
        }

        $error = [];
        foreach ($mids as $mid) {
            $pm = new PrivMsg($mid, 'id');
            try {
                $pm->deleteByUser($this->user->id);
            } catch (Exception $e) {
                $this->logger->error('failed to delete message ' . $mid . ' as user ' . $this->user->id);
                $error[] = 'failed to delete message ' . $mid;
            }
        }

        $this->json($error ? ['error' => $error] : null);
    }

    private function getMessage(int $mid): array
    {
        if ($mid > 0) {
            $pm = new PrivMsg();
            $msgs = $pm->getPMConversation($mid, $this->user->id);
            if (!$msgs) {
                throw new ErrorMessage('错误：该条短信不存在。');
            }

            return ['msgs' => $msgs, 'replyTo' => $pm->getReplyTo($mid, $this->user->id)];
        } else {
            throw new ErrorMessage('message does not exist');
        }
    }

    private function getMessageList(string $mailbox): array
    {
        if (!in_array($mailbox, self::$mailbox)) {
            throw new ErrorMessage('mailbox does not exist: ' . $mailbox);
        }

        $pmCount = $this->user->getPrivMsgsCount($mailbox);

        list($pageNo, $pageCount) = $this->getPagerInfo($pmCount, self::TOPICS_PER_PAGE);
        $msgs = $pmCount > 0 ? $this->user->getPrivMsgs($mailbox, self::TOPICS_PER_PAGE, ($pageNo - 1) * self::TOPICS_PER_PAGE) : [];
        // convert 'msgId' => 'mid',
        foreach ($msgs as $i => $m) {
            $msgs[$i]['mid'] = $m['msgId'];
            unset($msgs[$i]['msgId']);
        }
        return ['msgs' => $msgs, 'pager' => ['pageNo' => $pageNo, 'pageCount' => $pageCount]];
    }

    private function getNewMessageCount(): array
    {
        return ['count' => $this->user->getPrivMsgsCount('new')];
    }
}
