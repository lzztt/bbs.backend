<?php

namespace site\api;

use lzx\core\Mailer;
use site\Service;
use site\dbobject\PrivMsg;
use site\dbobject\User;

class MessageAPI extends Service
{
    const TOPICS_PER_PAGE = 25;

    private static $_mailbox = ['inbox', 'sent'];

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

        if (\is_numeric($this->args[0])) {
            $return = $this->_getMessage((int) $this->args[0]);
        } else {
            if ($this->args[0] == 'new') {
                $return = $this->_getNewMessageCount();
            } else {
                $return = $this->_getMessageList($this->args[0]);
            }
        }

        $this->_json($return);
    }

    /**
     * send a private message to user
     * uri: /api/message[?action=post]
     * post: toUID=<toUID>&body=<body>(&topicMID=<topicMID>)
     * return: new created message
     */
    public function post()
    {
        if (!$this->request->uid) {
            $this->error('您必须先登录，才能发送站内短信');
        }

        $topicMID = null;
        if (\array_key_exists('topicMID', $this->request->post)) {
            $topicMID = (int) $this->request->post['topicMID'];
            if ($topicMID <= 0) {
                $topicMID = null;
            }
        }
        $pm = new PrivMsg();

        // validate toUID
        $toUID = (int) $this->request->post['toUID'];
        if ($toUID) {
            if ($toUID == $this->request->uid) {
                $this->error('不能给自己发送站内短信');
            }

            if ($topicMID) {
                // reply an existing message topic
                $toUser = $pm->getReplyTo($topicMID, $this->request->uid);

                if (!$toUser) {
                    $this->error('短信不存在，未找到短信收信人');
                }

                if ($toUser['id'] != $toUID) {
                    $this->error('收件人帐号不匹配，无法发送短信');
                }
            }
        } else {
            $this->error('未指定短信收件人，无法发送短信');
        }

        $user = new User($toUID, 'username,email');

        if (!$user->exists()) {
            $this->error('收信人用户不存在');
        }

        // save pm to database
        if (\strlen($this->request->post['body']) < 5) {
            $this->error('短信正文需最少5个字母或3个汉字');
        }

        $pm->fromUID = $this->request->uid;
        $pm->toUID = $user->id;
        $pm->body = $this->request->post['body'];
        $pm->time = $this->request->timestamp;
        if ($topicMID) {
            // reply an existing message topic
            $pm->msgID = $topicMID;
            $pm->add();
        } else {
            // start a new message topic
            $pm->add();
            $pm->msgID = $pm->id;
            $pm->update('msgID');
        }

        if ($user->email) {
            $mailer = new Mailer('pm');
            $mailer->to = $user->email;
            $mailer->subject = $user->username . ' 您有一封新的站内短信';
            $mailer->body = $user->username . ' 您有一封新的站内短信' . "\n" . '请登录后点击下面链接阅读' . "\n" . 'https://' . $this->request->domain . '/app/user/pm/' . $pm->msgID;
            if (!$mailer->send()) {
                $this->logger->error('PM EMAIL REMINDER SENDING ERROR: ' . $pm->id);
            }
        }

        $sender = new User($this->request->uid, 'username,avatar');
        $this->_json([
            'id'         => $pm->id,
            'mid'        => $pm->msgID,
            'time'      => $pm->time,
            'body'      => $pm->body,
            'uid'        => $pm->fromUID,
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

        foreach (\explode(',', $this->args[0]) as $mid) {
            if (\is_numeric($mid) && \intval($mid) > 0) {
                $mids[] = (int) $mid;
            }
        }

        $error = [];
        foreach ($mids as $mid) {
            $pm = new PrivMsg($mid, null);
            try {
                $pm->deleteByUser($this->request->uid);
            } catch (\Exception $e) {
                $this->logger->error('failed to delete message ' . $mid . ' as user ' . $this->request->uid);
                $error[] = 'failed to delete message ' . $mid;
            }
        }

        $this->_json($error ? ['error' => $error] : null);
    }

    private function _getMessage($mid)
    {
        if ($mid > 0) {
            $pm = new PrivMsg();
            $msgs = $pm->getPMConversation($mid, $this->request->uid);
            if (empty($msgs)) {
                $this->error('错误：该条短信不存在。');
            }

            foreach ($msgs as $i => $m) {
                if (empty($m['avatar'])) {
                    $msgs[$i]['avatar'] = '/data/avatars/avatar0' . \mt_rand(1, 5) . '.jpg';
                }
            }

            return ['msgs' => $msgs, 'replyTo' => $pm->getReplyTo($mid, $this->request->uid)];
        } else {
            $this->error('message does not exist');
        }
    }

    private function _getMessageList($mailbox)
    {
        $user = new User($this->request->uid, null);
        if (!\in_array($mailbox, self::$_mailbox)) {
            $this->error('mailbox does not exist: ' . $mailbox);
        }

        $pmCount = $user->getPrivMsgsCount($mailbox);

        list($pageNo, $pageCount) = $this->_getPagerInfo($pmCount, self::TOPICS_PER_PAGE);
        $msgs = $pmCount > 0 ? $user->getPrivMsgs($mailbox, self::TOPICS_PER_PAGE, ($pageNo - 1) * self::TOPICS_PER_PAGE) : [];

        return ['msgs' => $msgs, 'pager' => ['pageNo' => $pageNo, 'pageCount' => $pageCount]];
    }

    private function _getNewMessageCount()
    {
        $user = new User($this->request->uid, null);
        return ['count' => $user->getPrivMsgsCount('new')];
    }
}

//__END_OF_FILE__
