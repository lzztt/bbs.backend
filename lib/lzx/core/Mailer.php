<?php declare(strict_types=1);

namespace lzx\core;

class Mailer
{
    public $domain;
    public $from;
    public $to;
    public $cc;
    public $bcc;
    public $subject;
    public $is_html = false;
    public $body;
    public $signature;

    public function __construct($from = 'noreply', $domain = null)
    {
        if ($domain) {
            $this->domain = $domain;
        } else {
            $this->domain = implode('.', array_slice(explode('.', $_SERVER['HTTP_HOST']), -2));
        }
        $this->from = $from;
    }

    public function send()
    {
        $headers = 'From: ' . $this->from . '@' . $this->domain . \PHP_EOL .
            'Reply-To: ' . $this->from . '@' . $this->domain . \PHP_EOL .
            'Sender: ' . $this->from . '@' . $this->domain . \PHP_EOL .
            ($this->cc ? 'Cc: ' . $this->cc . \PHP_EOL : '') .
            ($this->bcc ? 'Bcc: ' . $this->bcc . \PHP_EOL : '') .
            'MIME-Version: 1.0' . \PHP_EOL .
            'Content-Type: ' . ($this->is_html ? 'text/html; charset=utf-8' : 'text/plain; charset=utf-8; format=flowed; delsp=yes') . \PHP_EOL .
            'X-Mailer: WebMailer';

        if (!(isset($this->to) && isset($this->subject) && isset($this->body))) {
            return false;
        }

        $subject = "=?UTF-8?B?" . \base64_encode(trim(str_replace(["\r", \PHP_EOL, "\r\n"], "", $this->subject))) . "?=";
        $body = $this->body . $this->signature;

        if (mail($this->to, $subject, $body, $headers, '-f ' . $this->from . '@' . $this->domain)) {
            return true;
        } else {
            return false;
        }
    }
}
