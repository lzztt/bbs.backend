<?php

declare(strict_types=1);

namespace site\handler\mail\send;

use lzx\exception\Forbidden;
use lzx\html\Template;
use site\Controller;
use site\gen\theme\roselife\Mail;
use Swift_SendmailTransport;
use Swift_Mailer;
use Swift_Message;

class Handler extends Controller
{
    public function run(): void
    {
        $this->validateUser();

        if ($this->user->id !== self::UID_ADMIN) {
            throw new Forbidden();
        }

        if (!$this->request->data) {
            $this->html->setContent(new Mail());
        } else {
            $transport = new Swift_SendmailTransport('/usr/sbin/sendmail -t');
            // Create the Mailer using your created Transport
            $mailer = new Swift_Mailer($transport);

            $data = (object) $this->request->data;
            // Create a message
            $message = (new Swift_Message($data->subject))
                ->setFrom([$data->from])
                ->setTo([$data->to])
                ->setBcc([$data->bcc])
                ->setBody($data->body);

            $msgId = $message->getHeaders()->get('Message-ID');
            $msgId->setId(explode('@', $msgId->getId())[0] . '@' . explode('@', $data->from)[1]);

            // Send the message
            $result = $mailer->send($message);

            $this->html->setContent(Template::fromStr('sent mail count: ' . $result));
        }
    }
}
