<?php

namespace site\controller\single;

use site\controller\Single;
use site\dbobject\FFSubscriber;
use lzx\core\Mailer;

/**
 * @property \lzx\db\DB $db database object
 */
class SubscribeCtrler extends Single
{

   public function run()
   {
      if ( empty( $this->request->post[ 'email' ] ) )
      {
         echo '<span style="color:#B22222">错误</span>: E-Mail地址不能为空';
         exit;
      }


      $subscriber = new FFSubscriber();

      $subscriber->email = $this->request->post[ 'email' ];
      $subscriber->time = $this->request->timestamp;
      $subscriber->add();


      $mailer = new Mailer();

      $mailer->to = $subscriber->email;
      $mailer->subject = '您的单身活动关注已经收到';

      $mailer->body = "rt~\n感谢您关注单身活动";
      echo '<script type="text/javascript">$("#footer").load("/single/footer");</script>';

      if ( $mailer->send() )
      {
         echo '关注成功，确认邮件已发送';
      }
      else
      {
         echo '<span style="color:#B22222">错误</span>: 关注确认邮件发送失败';
      }

      exit;
   }

}

//__END_OF_FILE__