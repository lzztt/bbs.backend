<?php

declare(strict_types=1);

namespace site\handler\mail\send;

use lzx\exception\Forbidden;
use lzx\html\Template;
use site\Controller;
use site\gen\theme\roselife\Mail;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

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
            $transport = Transport::fromDsn('sendmail://default');
            // Create the Mailer using your created Transport
            $mailer = new Mailer($transport);

            $data = (object) $this->request->data;
            // Create a message
            $message = (new Email())
                ->from($data->from)
                ->to($data->to)
                ->bcc($data->bcc)
                ->subject($data->subject)
                ->text($data->body);

            // Send the message
            $mailer->send($message);

            $this->html->setContent(Template::fromStr('mail sent'));
        }
    }
}
