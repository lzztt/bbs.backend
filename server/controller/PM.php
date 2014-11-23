<?php

namespace site\controller;

use site\Controller;
use lzx\html\Template;

abstract class PM extends Controller
{

   const TOPIC_PER_PAGE = 25;

   protected function _getMailBoxLinks( $activeLink )
   {
      return Template::navbar( [
            '收件箱' => '/pm/mailbox/inbox',
            '发件箱' => '/pm/mailbox/sent'
            ], $activeLink
      );
   }

}

//__END_OF_FILE__
