<?php

declare(strict_types=1);

namespace lzx\core;

use InvalidArgumentException;
use UnexpectedValueException;

class Mailer
{
    private $from;
    private $to;
    private $cc;
    private $bcc;
    private $subject;
    private $body;
    private $isHtml = false;
    private $unsubscribe;

    public function __construct(string $from = 'noreply')
    {
        if (!$from) {
            $this->from = 'noreply@' . self::getDomain();
        } else {
            $this->from = strpos($from, '@') === false ? $from . '@' . self::getDomain() : $from;
            if (filter_var($this->from, FILTER_VALIDATE_EMAIL) === false) {
                throw new InvalidArgumentException($from);
            }
        }
    }

    private static function getDomain(): string
    {
        return implode('.', array_slice(explode('.', $_SERVER['SERVER_NAME']), -2));
    }

    public function setTo(string $email): void
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException($email);
        }
        $this->to = $email;
    }

    public function setCc(string $email): void
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException($email);
        }
        $this->cc = $email;
    }

    public function setBcc(string $email): void
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException($email);
        }
        $this->bcc = $email;
    }

    public function setSubject(string $subject): void
    {
        if (!$subject || strlen($subject) > 150 || strpos($subject, "\r") !== false || strpos($subject, "\n") !== false) {
            throw new InvalidArgumentException($subject);
        }
        $this->subject = trim($subject);
    }

    public function setBody(string $body, bool $isHtml = false): void
    {
        if (!$body) {
            throw new InvalidArgumentException($body);
        }
        $this->body = trim($body);
        $this->isHtml = $isHtml;
    }

    public function setUnsubscribe(string $url): void
    {
        $this->unsubscribe = $url;
    }

    public function send(): bool
    {
        if (!($this->to && $this->subject && $this->body)) {
            throw new UnexpectedValueException('to, or subject, or body is empty');
        }

        $headers = 'From: ' . $this->from . PHP_EOL .
            'Reply-To: ' . $this->from . PHP_EOL .
            'Sender: ' . $this->from . PHP_EOL .
            ($this->cc ? 'Cc: ' . $this->cc . PHP_EOL : '') .
            ($this->bcc ? 'Bcc: ' . $this->bcc . PHP_EOL : '') .
            'MIME-Version: 1.0' . PHP_EOL .
            'Content-Type: ' . ($this->isHtml ? 'text/html; charset=utf-8' : 'text/plain; charset=utf-8; format=flowed; delsp=yes') . PHP_EOL .
            'X-Mailer: WebMailer';
        if ($this->unsubscribe) {
            $headers = $headers . PHP_EOL .
                'List-Unsubscribe: <mailto:unsubscribe@' . array_pop(explode('@', $this->from)) . '?subject=unsubscribe>, <' . $this->unsubscribe . '>';
        }

        $subject = "=?UTF-8?B?" . base64_encode($this->subject) . "?=";

        return mail($this->to, $subject, $this->body, $headers, '-f ' . $this->from);
    }
}
