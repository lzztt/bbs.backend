<?php declare(strict_types=1);

namespace site\handler\api\message;

use Exception;
use lzx\core\Mailer;
use site\Service;
use site\dbobject\PrivMsg;
use site\dbobject\User;

class Handler extends Service
{
    const TOPICS_PER_PAGE = 25;

    private static $mailbox = ['inbox', 'sent'];

    /**
     * get private messages in user's mailbox (inbox,sent)
     * uri: /api/message/<mailbox>
     *        /api/message/<mailbox>?p=<pageNo>
     *
     * get private message
     * uri: /api/message/<mid>
     *
     * get new message count
     * uri: /api/message/new
     */
    public function get()
    {
        if (!$this->request->uid || empty($this->args)) {
            $this->forbidden();
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
    public function post()
    {
        if (!$this->request->uid) {
            $this->error('您必须先登录，才能发送站内短信');
        }

        if (array_key_exists('topicMID', $this->request->post)) {
            $this->request->post['topicMid'] = $this->request->post['topicMID'];
        }

        if (array_key_exists('toUID', $this->request->post)) {
            $this->request->post['toUid'] = $this->request->post['toUID'];
        }

        $topicMid = null;
        if (array_key_exists('topicMid', $this->request->post)) {
            $topicMid = (int) $this->request->post['topicMid'];
            if ($topicMid <= 0) {
                $topicMid = null;
            }
        }
        $pm = new PrivMsg();

        // validate toUid
        $toUid = (int) $this->request->post['toUid'];
        if ($toUid) {
            if ($toUid == $this->request->uid) {
                $this->error('不能给自己发送站内短信');
            }

            if ($topicMid) {
                // reply an existing message topic
                $toUser = $pm->getReplyTo($topicMid, $this->request->uid);

                if (!$toUser) {
                    $this->error('短信不存在，未找到短信收信人');
                }

                if ($toUser['id'] != $toUid) {
                    $this->error('收件人帐号不匹配，无法发送短信');
                }
            }
        } else {
            $this->error('未指定短信收件人，无法发送短信');
        }

        $user = new User($toUid, 'username,email');

        if (!$user->exists()) {
            $this->error('收信人用户不存在');
        }

        // save pm to database
        if (strlen($this->request->post['body']) < 5) {
            $this->error('短信正文需最少5个字母或3个汉字');
        }

        $pm->fromUid = $this->request->uid;
        $pm->toUid = $user->id;
        $pm->body = $this->request->post['body'];
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
            $mailer->to = $user->email;
            $mailer->subject = $user->username . ' 您有一封新的站内短信';
            $mailer->body = $user->username . ' 您有一封新的站内短信' . "\n" . '请登录后点击下面链接阅读' . "\n" . 'https://' . $this->request->domain . '/app/user/pm/' . $pm->msgId;
            if (!$mailer->send()) {
                $this->logger->error('PM EMAIL REMINDER SENDING ERROR: ' . $pm->id);
            }
        }

        $sender = new User($this->request->uid, 'username,avatar');
        $this->json([
            'id'         => $pm->id,
            'mid'        => $pm->msgId,
            'time'      => $pm->time,
            'body'      => $pm->body,
            'uid'        => $pm->fromUid,
            'username' => $sender->username,
            'avatar'    => $sender->avatar
        ]);
    }

    /**
     * delete a private message from user's message box
     * uri: /api/message/<mid>(,<mid>,...)?action=delete
     */
    public function delete()
    {
        if (!$this->request->uid || empty($this->args)) {
            $this->forbidden();
        }

        $mids = [];

        foreach (explode(',', $this->args[0]) as $mid) {
            if (is_numeric($mid) && intval($mid) > 0) {
                $mids[] = (int) $mid;
            }
        }

        $error = [];
        foreach ($mids as $mid) {
            $pm = new PrivMsg($mid, null);
            try {
                $pm->deleteByUser($this->request->uid);
            } catch (Exception $e) {
                $this->logger->error('failed to delete message ' . $mid . ' as user ' . $this->request->uid);
                $error[] = 'failed to delete message ' . $mid;
            }
        }

        $this->json($error ? ['error' => $error] : null);
    }

    private function getMessage($mid)
    {
        if ($mid > 0) {
            $pm = new PrivMsg();
            $msgs = $pm->getPMConversation($mid, $this->request->uid);
            if (empty($msgs)) {
                $this->error('错误：该条短信不存在。');
            }

            foreach ($msgs as $i => $m) {
                if (empty($m['avatar'])) {
                    $msgs[$i]['avatar'] = '/data/avatars/avatar0' . rand(1, 5) . '.jpg';
                }
            }

            return ['msgs' => $msgs, 'replyTo' => $pm->getReplyTo($mid, $this->request->uid)];
        } else {
            $this->error('message does not exist');
        }
    }

    private function getMessageList($mailbox)
    {
        $user = new User($this->request->uid, null);
        if (!in_array($mailbox, self::$mailbox)) {
            $this->error('mailbox does not exist: ' . $mailbox);
        }

        $pmCount = $user->getPrivMsgsCount($mailbox);

        list($pageNo, $pageCount) = $this->getPagerInfo($pmCount, self::TOPICS_PER_PAGE);
        $msgs = $pmCount > 0 ? $user->getPrivMsgs($mailbox, self::TOPICS_PER_PAGE, ($pageNo - 1) * self::TOPICS_PER_PAGE) : [];

        return ['msgs' => $msgs, 'pager' => ['pageNo' => $pageNo, 'pageCount' => $pageCount]];
    }

    private function getNewMessageCount()
    {
        $user = new User($this->request->uid, null);
        return ['count' => $user->getPrivMsgsCount('new')];
    }
}
