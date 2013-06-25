<?php

namespace lzx\core;

class Mailer
{
   public $domain;
   public $from;
   public $to;
   public $subject;
   public $is_html = FALSE;
   public $body;
   public $signature = "\n\n-----------------\nThe HoustonBBS Team";

   public function __construct($domain = NULL, $from = 'noreply')
   {
      if ($domain)
      {
         $this->domain = $domain;
      }
      else
      {
         $this->domain = \implode('.', \array_slice(\explode('.', $_SERVER['HTTP_HOST']), -2));
      }
      $this->from = $from;
   }

   public function send()
   {
      $headers = 'From: HoustonBBS <' . $this->from . '@' . $this->domain . '>' . \PHP_EOL .
         'Reply-To: ' . $this->from . '@' . $this->domain . \PHP_EOL .
         'Sender: ' . $this->from . '@' . $this->domain . \PHP_EOL .
         'MIME-Version: 1.0' . \PHP_EOL .
         'Content-Type: text/' . ($this->is_html ? 'html' : 'plain') . '; charset=utf-8; format=flowed; delsp=yes' . \PHP_EOL .
         'X-Mailer: HoustonBBSMailer';

      if (!(isset($this->to) && isset($this->subject) && isset($this->body)))
      {
         return FALSE;
      }

      $subject = "=?UTF-8?B?" . \base64_encode(trim(str_replace(array("\r", \PHP_EOL, "\r\n"), "", $this->subject))) . "?=";
      $body = $this->body . $this->signature;

      if (\mail($this->to, $subject, $body, $headers, '-f ' . $this->from . '@' . $this->domain))
      {
         return TRUE;
      }
      else
      {
         return FALSE;
      }
   }

}
