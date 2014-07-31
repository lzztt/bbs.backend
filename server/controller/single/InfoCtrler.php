<?php

namespace site\controller\single;

use site\controller\Single;
use lzx\html\Template;
use site\dbobject\FFAttendee;
use site\dbobject\FFQuestion;

/**
 * @property \lzx\db\DB $db database object
 */
class InfoCtrler extends Single
{

   // private attendee info
   public function run()
   {
      $uid = (int) $this->request->get[ 'u' ];
      $code = $this->request->get[ 'c' ];
      if ( $code != $this->_getCode( $uid ) )
      {
         \var_dump( $this->_getCode( $uid ) );
         $this->pageForbidden();
      }


      if ( $this->request->post )
      {
         foreach ( $this->request->post[ 'question' ] as $q )
         {
            if ( $q )
            {
               $question = new FFQuestion();
               $question->aid = (int) $this->request->post[ 'uid' ];
               $question->body = $q;
               $question->add();
            }
         }

         if ( $this->request->post[ 'info' ] )
         {
            $a = new FFAttendee();
            $a->id = $this->request->post[ 'uid' ];
            $a->info = $this->request->post[ 'info' ];
            $a->update( 'info' );
         }

         $this->html->var[ 'content' ] = '<div id="activity">您的信息已经被保存。<br /><a href="/single">返回首页</a></div>';
      }
      else
      {
         $this->html->var[ 'content' ] = new Template( 'info', [ 'uid' => $uid, 'action' => $this->request->uri ] );
      }
   }

   protected function _getCode( $uid )
   {
      return \crc32( \substr( \md5( 'alexmika' . $uid ), 5, 10 ) );
   }

}

//__END_OF_FILE__
