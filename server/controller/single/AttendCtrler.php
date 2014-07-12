<?php

namespace site\controller\single;

use site\controller\Single;
use site\dbobject\FFAttendee;
use site\dbobject\FFComment;
use lzx\html\Template;
use lzx\core\Mailer;

/**
 * @property \lzx\db\DB $db database object
 */
class AttendCtrler extends Single
{

   // attend activity
   public function run()
   {
      if ( \file_exists( $this->config->path[ 'file' ] . '/ffmy.msg' ) )
      {
         echo '<span style="color:#B22222">错误</span>: ' . \file_get_contents( $this->config->path[ 'file' ] . '/ffmy.msg' );
         exit;
      }

      if ( empty( $this->request->post[ 'name' ] ) || \strlen( $this->request->post[ 'sex' ] ) < 1 || empty( $this->request->post[ 'age' ] ) || empty( $this->request->post[ 'email' ] ) )
      {
         echo '<span style="color:#B22222">错误</span>: 带星号(<span class="form_required" title="此项必填。">*</span>)选项为必填选项';
         exit;
      }


      $attendee = new FFAttendee();

      $attendee->name = $this->request->post[ 'name' ];
      $attendee->sex = $this->request->post[ 'sex' ];
      $attendee->age = $this->request->post[ 'age' ];
      $attendee->email = $this->request->post[ 'email' ];
      $attendee->phone = $this->request->post[ 'phone' ];

      if ( $this->request->post[ 'comment' ] )
      {

         $comment = new FFComment();
         $comment->name = $this->request->post[ 'anonymous' ] ? $this->request->ip : $this->request->post[ 'name' ];
         $comment->body = $this->request->post[ 'comment' ];
         $comment->time = $this->request->timestamp;
         $comment->add();
         $attendee->cid = $comment->id;
      }

      $attendee->time = $this->request->timestamp;
      $attendee->add();


      $mailer = new Mailer();

      $mailer->to = $attendee->email;
      $mailer->subject = $attendee->name . '，您的单身活动报名已经收到';

      $count = \array_pop( $this->db->query( 'CALL get_attendee_count_single(' . $this->thirty_two_start . ')' ) );
      $contents = [
         'name' => $attendee->name,
         'male' => $count[ 'male' ],
         'female' => $count[ 'female' ]
      ];

      $mailer->body = new Template( 'mail/attendee', $contents );

      echo '<script type="text/javascript">$("#footer").load("/single/footer");</script>';

//       echo '报名成功，但因为8月6号的七夕活动已经结束，确认邮件并未发送。您将在下次活动计划出来时收到带有活动详情的电子邮件确认';

      if ( $mailer->send() )
      {
         echo '报名成功，确认邮件已发送';
      }
      else
      {
         echo '<span style="color:#B22222">错误</span>: 报名确认邮件邮寄失败';
      }

      exit;
   }

}

//__END_OF_FILE__